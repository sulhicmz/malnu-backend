# DEPRECATED: Laravel 12 Application

## ⚠️ CRITICAL DEPRECATION NOTICE ⚠️

**This application is DEPRECATED and will be removed in future releases.**

### Current Status
- **Status**: DEPRECATED - DO NOT USE FOR NEW DEVELOPMENT
- **Last Updated**: November 2025
- **Migration Target**: All functionality should be migrated to the main HyperVel application in the root directory
- **Removal Date**: TBD (to be determined after migration completion)

### Why is this being deprecated?
This Laravel 12 modular application exists alongside the main HyperVel application in the root directory, creating:
- Confusion about which application to use
- Maintenance overhead for two separate codebases
- Potential for feature divergence
- Increased complexity for developers

### What should you do?
- **DO NOT** start new development in this application
- **DO NOT** add new features to this codebase
- **DO** focus all development efforts on the main HyperVel application in the root directory
- **DO** migrate any unique functionality found here to the main application if needed

### Migration Path
1. Review the unique modules in this application:
   - ERaport (Report Management)
   - LaporanAnalitik (Analytical Reporting) 
   - ManajemenSekolah (School Management)
   - SistemMonetisasi (Monetization System)
2. If you need functionality from these modules, plan to implement equivalent features in the main HyperVel application
3. Update any dependencies or integrations to point to the main application

### Technical Details
- Framework: Laravel 12 with modular architecture (nwidart/laravel-modules)
- PHP Version: ^8.2
- Modules: ERaport, LaporanAnalitik, ManajemenSekolah, SistemMonetisasi

### Contact
For questions about this deprecation, please contact the development team or refer to the main documentation in the root directory.
