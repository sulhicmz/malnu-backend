# Progressive Web App (PWA) Implementation

This document describes the Progressive Web App (PWA) implementation for the Malnu School Management System frontend.

## Overview

The PWA implementation provides:
- **Offline Support**: Core functionality works without internet connection
- **Installability**: Users can install the app on their home screen
- **Push Notifications**: Real-time alerts for important updates
- **Background Sync**: Automatic data synchronization when connection is restored
- **Performance**: Optimized caching strategies for fast load times

## Architecture

### Core Components

1. **Service Worker** (`vite-plugin-pwa`)
   - Generated automatically by Vite PWA plugin
   - Handles caching strategies
   - Manages background sync
   - Controls update lifecycle

2. **Web App Manifest** (`public/manifest.json`)
   - Defines app metadata (name, icons, theme)
   - Configures installation behavior
   - Specifies shortcuts and screenshots

3. **Registration** (`src/main.tsx`)
   - Registers service worker on app load
   - Handles update notifications
   - Manages notification permissions

4. **React Hooks** (`src/hooks/usePWA.ts`)
   - `usePWA()`: Main PWA state management
   - `useNotificationPermission()`: Notification permission handling
   - `useNotification()`: Send notifications
   - `useBackgroundSync()`: Background sync management
   - `useServiceWorkerUpdate()`: Service worker update handling

5. **UI Components** (`src/components/PWAInstallButton.tsx`)
   - Install button with multiple variants
   - Installation state indicators

## Features

### 1. Offline Caching

The service worker implements multiple caching strategies:

- **CacheFirst**: Fonts and images (rarely change)
- **StaleWhileRevalidate**: JS/CSS files (update in background)
- **NetworkFirst**: API calls (fallback to cache when offline)

### 2. Auto-Update

- Service worker checks for updates every hour
- Users are prompted when updates are available
- Optional automatic reload on update

### 3. Install Prompt

- Custom install button in navbar
- Banner variant for prominent display
- Minimal variant for icon-only display

### 4. Push Notifications

- Permission requested on first load
- Hooks for sending notifications
- Support for rich notifications with actions

### 5. Background Sync

- Deferred actions queue when offline
- Automatic sync when connection restored
- Supported for form submissions and data updates

## Configuration

### Vite Config (`vite.config.ts`)

```typescript
VitePWA({
  registerType: 'autoUpdate',
  includeAssets: ['favicon.ico', 'apple-touch-icon.png'],
  manifest: {
    name: 'Malnu School Management',
    short_name: 'Malnu SMS',
    // ... manifest configuration
  },
  workbox: {
    globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
    runtimeCaching: [
      // Caching strategies
    ],
  },
})
```

### Manifest (`public/manifest.json`)

Key fields:
- `name` / `short_name`: App display names
- `theme_color` / `background_color`: App colors
- `display`: `standalone` for app-like experience
- `icons`: App icons in multiple sizes
- `shortcuts`: Quick actions from home screen
- `screenshots`: Store/app listing images

## Usage

### Using PWA Hooks

```typescript
import { usePWA, useNotification } from '../hooks/usePWA';

function MyComponent() {
  const { isInstallable, isInstalled, isOffline, promptInstall } = usePWA({
    onInstall: () => console.log('App installed!'),
    onOffline: () => console.log('Gone offline'),
    onOnline: () => console.log('Back online'),
  });

  const { sendNotification } = useNotification();

  return (
    <div>
      {isOffline && <span>Offline Mode</span>}
      {isInstallable && !isInstalled && (
        <button onClick={promptInstall}>Install App</button>
      )}
    </div>
  );
}
```

### Using Install Button

```typescript
import { PWAInstallButton } from '../components/PWAInstallButton';

// Button variant (default)
<PWAInstallButton />

// Banner variant (prominent)
<PWAInstallButton variant="banner" />

// Minimal variant (icon only)
<PWAInstallButton variant="minimal" />
```

## Browser Support

| Feature | Chrome | Firefox | Safari | Edge |
|---------|--------|---------|--------|------|
| Service Worker | ✅ | ✅ | ✅ | ✅ |
| Install Prompt | ✅ | ❌ | ❌ (iOS only) | ✅ |
| Push Notifications | ✅ | ✅ | ❌ | ✅ |
| Background Sync | ✅ | ❌ | ❌ | ✅ |

## Testing

### Local Testing

```bash
# Build the app
npm run build

# Preview with service worker
npm run preview
```

### Chrome DevTools

1. Open DevTools → Application tab
2. Check Service Workers section
3. Verify manifest in Manifest section
4. Test offline mode in Network tab

### Lighthouse Audit

Run Lighthouse PWA audit to verify:
- Service worker registration
- Manifest validity
- Offline functionality
- Performance metrics

## Performance Considerations

### Bundle Optimization

- Code splitting by vendor (React, Router, Charts, Utils)
- Lazy loading for routes
- Image optimization

### Caching Strategy

- Aggressive caching for static assets (365 days)
- Moderate caching for images (30 days)
- Short caching for API responses (24 hours)

### Network Efficiency

- Network-first for API calls (fast updates)
- Cache-first for fonts (rarely change)
- Stale-while-revalidate for JS/CSS (background updates)

## Troubleshooting

### Service Worker Not Registering

1. Check HTTPS (required for production)
2. Verify `vite-plugin-pwa` is installed
3. Check browser console for errors
4. Ensure `virtual:pwa-register` types are loaded

### Updates Not Applying

1. Check service worker update interval
2. Verify `skipWaiting` is enabled
3. Clear browser cache and reload
4. Check DevTools → Application → Service Workers

### Install Prompt Not Showing

1. Verify PWA criteria met (HTTPS, manifest, service worker)
2. Check browser support for beforeinstallprompt
3. Ensure user hasn't dismissed prompt recently
4. Check Chrome flags on desktop (chrome://flags/#enable-desktop-pwas)

## Security

- Service workers require HTTPS in production
- Push notifications require user permission
- Background sync respects user data limits
- Caching strategies respect Cache-Control headers

## Future Enhancements

1. **Native Share API**: Share content with other apps
2. **File System Access**: Import/export student data
3. **Wake Lock**: Keep screen on during attendance
4. **Periodic Background Sync**: Daily data sync
5. **Badging API**: Show notification counts on app icon

## References

- [Vite PWA Plugin Documentation](https://vite-pwa-org.netlify.app/)
- [Workbox Documentation](https://developer.chrome.com/docs/workbox/)
- [Web App Manifest Spec](https://w3c.github.io/manifest/)
- [Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- [Progressive Web Apps](https://web.dev/progressive-web-apps/)

## Changelog

### 2025-02-17
- Initial PWA implementation
- Service worker with offline caching
- Web app manifest with shortcuts
- React hooks for PWA functionality
- Install button component
- Auto-update mechanism
- Push notification support
- Background sync preparation
