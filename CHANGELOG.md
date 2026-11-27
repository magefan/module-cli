### Added
- The admin command line now shows the exact command being run (e.g. "Executing: bin/magento cache:flush") before its output.
- The admin command line now shows clear error messages for failed requests, such as an expired form key, a timeout, a lost connection or an invalid server response.

### Changed
- The admin command line now runs only Magento commands (bin/magento ...), and anything else entered is rejected with a message. (#16542)
- The "PHP Command" setting now accepts only a path to the PHP binary, and the options -dmemory_limit=-1 -ddisplay_errors=1 are added to every command automatically. (#16542)
- Output of commands that fail is now shown in the admin command line like in a terminal, instead of a generic error. (#15453)

### Security
- Commands entered in the admin command line are no longer passed to the shell as raw text: shell operators are stripped and every argument is escaped, which prevents shell command injection. (#15453)
- Admin user commands (admin:user:*) are blocked in the admin command line, including abbreviated or upper-case forms. (#15453)
- Command output is now HTML-escaped before it is shown in the admin, and the command endpoint accepts only POST requests. (#15453)
- Saving the configuration now rejects a "PHP Command" value that is not a PHP binary, so other server programs cannot be run through it. (#16542)
- "PHP Command" now accepts only a full path to the PHP binary (e.g. /usr/bin/php8.4) or its name (e.g. php8.4), so PHP tools such as phpunit or files in Magento folders cannot be used as the PHP binary.
- "Allowed Commands For Managers" and "Most Used Magento 2 Commands" now accept only names of existing Magento commands, and admin users without "Command Line Admin" access can run only an allowed command name without arguments.
- The admin password for the command line is now accepted only in the POST body, so it never appears in URLs or web server logs.

### Included modules

#### magefan/module-admin-user-guide
- Added: Added a redesigned Admin Guide panel to admin pages, with a header, a list of help articles for the current page and a link to the extension settings (#12446).
- Added: Added UTM tracking parameters to Magefan links in the Admin Guide panel.
- Fixed: Fixed admin pages hanging or returning a 504 error when the Magefan knowledge-base server cannot be reached: the request now times out quickly, waits an hour before trying again and keeps the saved guide data instead of replacing it with an error page (#16362).
