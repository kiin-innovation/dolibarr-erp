# DoliDiag Dolibarr Module

## Overview
DoliDiag is a diagnostic module for Dolibarr ERP/CRM that generates detailed reports about the system environment, Dolibarr configuration, and installed modules. It is designed to assist web developers and support professionals in troubleshooting client Dolibarr installations.

## Features

- **System Information**: PHP version, configuration settings, loaded extensions, web server details, and cURL status.
- **Dolibarr Core Information**: Version, installation type, activated core modules, key configuration settings, and user statistics.
- **Module Inventory**: List of installed modules (core and custom) with name, version, activation status, and dependency checks.
- **Database Information**: Database type, version, connection status, and integrity checks.
- **Security Status**: HTTPS enforcement, deprecated modules detection, and security recommendations.
- **Multiple Output Formats**: Generate reports in HTML, PDF, or Markdown format based on your preference.
- **Report Storage**: Saves reports in documents/dolidiag with secure permissions and lists previous reports for viewing/downloading.
- **Support Recommendations**: Automated analysis with practical suggestions for resolving detected issues.

## Installation

1. **Copy Files**: Place the dolidiag folder in htdocs/custom/ of your Dolibarr installation.
2. **Activate Module**:
   - Go to Home > Setup > Modules/Applications.
   - Find DoliDiag in the "Other" section and click Activate.
3. **Set Permissions**:
   - Ensure the documents/dolidiag directory is writable by the web server.
   - Verify users have the "Generate and view diagnostic reports" permission.
4. **Access Module**:
   - Navigate to Tools menu > DoliDiag to generate and view reports.
   - Configure diagnostic sections in Home > Setup > Other Setup > DoliDiag.

## Requirements

- **Dolibarr Version**: 14.0 or higher
- **PHP Version**: 7.1.0 or higher
- **Database**: MySQL, MariaDB, or PostgreSQL
- **Web Server**: Apache, Nginx, or compatible

## Usage

1. **Generate Report**:
   - In the DoliDiag interface, select whether to redact sensitive information.
   - Optionally, add a description of the issue you're investigating.
   - Click "Generate Report".
   - The report will be generated in your chosen format (configurable in settings).

2. **View/Download Reports**:
   - A table lists all generated reports with their creation date and filename.
   - Click Download to save a report in its original format (HTML, PDF, or Markdown).
   - Click Delete to remove stored reports.

3. **Configure Settings**:
   - In the setup page, enable/disable diagnostic sections.
   - Choose your preferred output format (PDF, HTML, or Markdown).
   - Configure whether sensitive data should be redacted by default.

## Directory Structure
```
dolidiag/
├── admin/
│   ├── dolidiag_setup.php    # Configuration page
├── class/
│   ├── dolidiag.class.php    # Report generation logic
├── core/
│   ├── modules/
│   │   ├── modDoliDiag.class.php # Module descriptor
├── lib/
│   ├── dolidiag.lib.php      # Helper functions
├── dolidiag.php              # Main interface
├── report_template.html      # HTML report template
├── README.md                 # This file
```

## Security Notes

- Access is restricted to users with appropriate permissions.
- All outputs are sanitized to prevent XSS vulnerabilities.
- Optional redaction of sensitive information (IP addresses, exact versions, etc.).
- Sensitive configuration data is not exposed in reports.

## Testing

- Tested on Dolibarr versions 14.0-21.0.1
- Compatible with DoliWamp and standard installations.
- Works with MySQL and MariaDB database systems.

## Support
For issues or feature requests, please contact the module developer at contact@dzprod.net.

Generated for Dolibarr versions 14.0 and higher | DoliDiag v1.0.0