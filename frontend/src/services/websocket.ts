// Simple browser-compatible EventEmitter implementation
type EventCallback = (...args: unknown[]) => void;

class EventEmitter {
  private listeners: Map<string, EventCallback[]> = new Map();

  on(event: string, callback: EventCallback): void {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, []);
    }
    this.listeners.get(event)!.push(callback);
  }

  emit(event: string, ...args: unknown[]): void {
    if (this.listeners.has(event)) {
      this.listeners.get(event)!.forEach(callback => callback(...args));
    }
  }

  off(event: string, callback?: EventCallback): void {
    if (!this.listeners.has(event)) return;
    if (callback) {
      const callbacks = this.listeners.get(event)!;
      this.listeners.set(event, callbacks.filter(cb => cb !== callback));
    } else {
      this.listeners.delete(event);
    }
  }
}

class WebSocketService extends EventEmitter {
  private ws: WebSocket | null = null;
  private url: string;
  private reconnectAttempts = 0;
  private maxReconnectAttempts = 5;
  private reconnectInterval = 5000;
  private heartbeatInterval: NodeJS.Timeout | null = null;
  private heartbeatTimeout: NodeJS.Timeout | null = null;
  private heartbeatTimeoutDuration = 5000;

  constructor(url: string) {
    super();
    this.url = url;
  }

  connect(): void {
    if (this.ws && (this.ws.readyState === WebSocket.OPEN || this.ws.readyState === WebSocket.CONNECTING)) {
      return;
    }

    try {
      this.ws = new WebSocket(this.url);
      
      this.ws.onopen = () => {
        this.reconnectAttempts = 0;
        this.emit('connected');
        this.startHeartbeat();
      };

      this.ws.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data);
          this.emit('message', data);
        } catch {
          this.emit('message', event.data);
        }
      };

      this.ws.onclose = (event) => {
        this.emit('disconnected', event);
        this.stopHeartbeat();
        this.attemptReconnect();
      };

      this.ws.onerror = (error) => {
        this.emit('error', error);
      };
    } catch (error) {
      this.emit('error', error);
      this.attemptReconnect();
    }
  }

  private attemptReconnect(): void {
    if (this.reconnectAttempts < this.maxReconnectAttempts) {
      this.reconnectAttempts++;
      setTimeout(() => {
        this.connect();
      }, this.reconnectInterval);
    } else {
      this.emit('max_reconnect_attempts');
    }
  }

  private startHeartbeat(): void {
    this.heartbeatInterval = setInterval(() => {
      if (this.ws && this.ws.readyState === WebSocket.OPEN) {
        this.ws.send(JSON.stringify({ type: 'ping' }));
        
        // Set timeout for pong response
        this.heartbeatTimeout = setTimeout(() => {
          this.ws?.close();
        }, this.heartbeatTimeoutDuration);
      }
    }, 30000); // Send ping every 30 seconds
  }

  private stopHeartbeat(): void {
    if (this.heartbeatInterval) {
      clearInterval(this.heartbeatInterval);
      this.heartbeatInterval = null;
    }
    
    if (this.heartbeatTimeout) {
      clearTimeout(this.heartbeatTimeout);
      this.heartbeatTimeout = null;
    }
  }

  send(data: unknown): void {
    if (this.ws && this.ws.readyState === WebSocket.OPEN) {
      this.ws.send(JSON.stringify(data));
    }
  }

  disconnect(): void {
    this.stopHeartbeat();
    
    if (this.ws) {
      this.ws.close();
      this.ws = null;
    }
    
    this.reconnectAttempts = this.maxReconnectAttempts; // Prevent auto-reconnect
  }

  isConnected(): boolean {
    return this.ws !== null && this.ws.readyState === WebSocket.OPEN;
  }
}

// Create a singleton instance
const wsService = new WebSocketService(
  import.meta.env.VITE_WS_URL || 'ws://localhost:9502'
);

export default wsService;