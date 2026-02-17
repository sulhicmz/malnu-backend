import { usePWA } from '../hooks/usePWA';
import { Download, Check, Smartphone } from 'lucide-react';
import { useState } from 'react';

interface PWAInstallButtonProps {
  variant?: 'button' | 'banner' | 'minimal';
  className?: string;
}

export function PWAInstallButton({ variant = 'button', className = '' }: PWAInstallButtonProps) {
  const { isInstallable, isInstalled, canInstall, promptInstall } = usePWA();
  const [isInstalling, setIsInstalling] = useState(false);

  const handleInstall = async () => {
    if (!canInstall) return;
    
    setIsInstalling(true);
    try {
      const result = await promptInstall();
      if (result.outcome === 'accepted') {
        console.log('User accepted PWA installation');
      } else {
        console.log('User dismissed PWA installation');
      }
    } catch (error) {
      console.error('PWA installation failed:', error);
    } finally {
      setIsInstalling(false);
    }
  };

  // Don't show if already installed
  if (isInstalled) {
    return (
      <div className={`flex items-center gap-2 text-green-600 ${className}`}>
        <Check className="w-4 h-4" />
        <span className="text-sm">App Installed</span>
      </div>
    );
  }

  // Don't show if not installable
  if (!isInstallable) {
    return (
      <div className={`flex items-center gap-2 text-gray-500 ${className}`}>
        <Smartphone className="w-4 h-4" />
        <span className="text-sm">Install available in supported browsers</span>
      </div>
    );
  }

  if (variant === 'banner') {
    return (
      <div className={`bg-blue-50 border border-blue-200 rounded-lg p-4 ${className}`}>
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="bg-blue-500 p-2 rounded-lg">
              <Smartphone className="w-5 h-5 text-white" />
            </div>
            <div>
              <h3 className="font-medium text-gray-900">Install Malnu School Management</h3>
              <p className="text-sm text-gray-600">Get quick access and offline support</p>
            </div>
          </div>
          <button
            onClick={handleInstall}
            disabled={isInstalling}
            className="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
          >
            <Download className="w-4 h-4" />
            {isInstalling ? 'Installing...' : 'Install'}
          </button>
        </div>
      </div>
    );
  }

  if (variant === 'minimal') {
    return (
      <button
        onClick={handleInstall}
        disabled={isInstalling}
        className={`p-2 hover:bg-gray-100 rounded-lg transition-colors ${className}`}
        title="Install App"
      >
        <Download className="w-5 h-5 text-gray-600" />
      </button>
    );
  }

  return (
    <button
      onClick={handleInstall}
      disabled={isInstalling}
      className={`bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors ${className}`}
    >
      <Download className="w-4 h-4" />
      {isInstalling ? 'Installing...' : 'Install App'}
    </button>
  );
}
