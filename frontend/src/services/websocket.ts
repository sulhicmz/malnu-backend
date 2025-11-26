interface WebSocketEventMap {
  connected: () => void;
  disconnected: (event: CloseEvent) => void;
  message: (data: any) => void;
  error: (error: any) => void; // Changed to 'any' to handle different error types
  max_reconnect_attempts: () => void;
}

class WebSocketService {
  private listeners: { [K in keyof WebSocketEventMap]?: Array<WebSocketEventMap[K]> } = {};
  private ws: WebSocket | null = null;
  private url: string;
  private reconnectAttempts = 0;
  private maxReconnectAttempts = 5;
  private reconnectInterval = 5000;
  private heartbeatInterval: number | null = null;
  private heartbeatTimeout: number | null = null;
  private heartbeatTimeoutDuration = 5000;

  constructor(url: string) {
    this.url = url;
  }

  on<K extends keyof WebSocketEventMap>(event: K, listener: WebSocketEventMap[K]): void {
    if (!this.listeners[event]) {
      this.listeners[event] = [];
    }
    (this.listeners[event] as Array<WebSocketEventMap[K]>).push(listener);
  }

  off<K extends keyof WebSocketEventMap>(event: K, listener: WebSocketEventMap[K]): void {
    if (!this.listeners[event]) return;
    const index = (this.listeners[event] as Array<WebSocketEventMap[K]>).indexOf(listener);
    if (index > -1) {
      (this.listeners[event] as Array<WebSocketEventMap[K]>).splice(index, 1);
    }
  }

  private emit<K extends keyof WebSocketEventMap>(event: K, ...args: Parameters<WebSocketEventMap[K]>): void {
    const eventListeners = this.listeners[event];
    if (eventListeners) {
      eventListeners.forEach(listener => {
        (listener as any)(...args);
      });
    }
  }

  connect(): void {
    if (this.ws && (this.ws.readyState === WebSocket.OPEN || this.ws.readyState === WebSocket.CONNECTING)) {
      return;
    }

    try {
      this.ws = new WebSocket(this.url);
      
      this.ws.onopen = () => {
        console.log('WebSocket connected');
        this.reconnectAttempts = 0;
        this.emit('connected');
        this.startHeartbeat();
      };

      this.ws.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data);
          this.emit('message', data);
        } catch (error) {
          console.error('Error parsing WebSocket message:', error);
          this.emit('message', event.data);
        }
      };

      this.ws.onclose = (event) => {
        console.log('WebSocket disconnected:', event.code, event.reason);
        this.emit('disconnected', event);
        this.stopHeartbeat();
        this.attemptReconnect();
      };

      this.ws.onerror = (error) => {
        console.error('WebSocket error:', error);
        this.emit('error', error);
      };
    } catch (error) {
      console.error('Failed to create WebSocket connection:', error);
      this.emit('error', error);
      this.attemptReconnect();
    }
  }

  private attemptReconnect(): void {
    if (this.reconnectAttempts < this.maxReconnectAttempts) {
      this.reconnectAttempts++;
      console.log(`Attempting to reconnect... (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
      
      setTimeout(() => {
        this.connect();
      }, this.reconnectInterval);
    } else {
      console.error('Max reconnection attempts reached');
      this.emit('max_reconnect_attempts');
    }
  }

  private startHeartbeat(): void {
    this.heartbeatInterval = window.setInterval(() => {
      if (this.ws && this.ws.readyState === WebSocket.OPEN) {
        this.ws.send(JSON.stringify({ type: 'ping' }));
        
        // Set timeout for pong response
        this.heartbeatTimeout = window.setTimeout(() => {
          console.warn('Heartbeat timeout, closing connection');
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

  send(data: any): void {
    if (this.ws && this.ws.readyState === WebSocket.OPEN) {
      this.ws.send(JSON.stringify(data));
    } else {
      console.warn('WebSocket not connected, cannot send data');
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