import { useEffect, useState } from 'react';
import wsService from '../services/websocket';

const useWebSocket = (onMessage?: (data: any) => void) => {
  const [isConnected, setIsConnected] = useState(false);
  const [connectionError, setConnectionError] = useState<string | null>(null);

  useEffect(() => {
    // Handle connection events
    const handleConnected = () => {
      setIsConnected(true);
      setConnectionError(null);
    };

    const handleDisconnected = (event: CloseEvent) => {
      setIsConnected(false);
      if (event.code !== 1000) { // 1000 means normal closure
        setConnectionError(`Disconnected: ${event.reason || 'Unknown error'}`);
      }
    };

    const handleError = (error: Event) => {
      setIsConnected(false);
      setConnectionError('Connection error occurred');
      console.error('WebSocket error:', error);
    };

    const handleMessage = (data: any) => {
      if (onMessage) {
        onMessage(data);
      }
    };

    // Subscribe to WebSocket events
    wsService.on('connected', handleConnected);
    wsService.on('disconnected', handleDisconnected);
    wsService.on('error', handleError);
    wsService.on('message', handleMessage);

    // Connect to WebSocket
    wsService.connect();

    // Cleanup function
    return () => {
      wsService.off('connected', handleConnected);
      wsService.off('disconnected', handleDisconnected);
      wsService.off('error', handleError);
      wsService.off('message', handleMessage);
    };
  }, [onMessage]);

  const sendMessage = (data: any) => {
    wsService.send(data);
  };

  return {
    isConnected,
    connectionError,
    sendMessage,
  };
};

export default useWebSocket;