WKHTMLTOPDF SETUP for MMRPG-PROTOTYPE
The library "wkhtmltopdf" allows MMRPG to export certain pages as PDF-documents for safe-keeping or sharing.
If the library is not installed, these features will simply not-function and be excluded from relevant menus.

1.  Download original compiled source(s) from appropriate for your system:
    - https://wkhtmltopdf.org/downloads.html

2.  Place compiled source(s) in appropriate directory:
    - .libs/wkhtmltopdf/{xxx}/ where {xxx} is the system name ("linux", "macos", "windows")

3.  Extract the required binary and place in same folder:
    - Linux: Extract binary from "wkhtmltox_xxx.deb" file
    - MacOS: Extract binary from "wkhtmltox_xxx.pkg" file
    - Windows: Extract binary from "wkhtmltox_xxx.zip" file

4.  Rename binary to "wkhtmltopdf" and make executable:
    - Linux: "mv <install-path>/wkhtmltopdf .libs/wkhtmltopdf/linux/wkhtmltopdf" && chmod +x .libs/wkhtmltopdf/linux/wkhtmltopdf
    - MacOS: "mv <install-path>/wkhtmltopdf .libs/wkhtmltopdf/macos/wkhtmltopdf" && chmod +x .libs/wkhtmltopdf/macos/wkhtmltopdf
    - Windows: "move <install-path>wkhtmltopdf.exe .libs/wkhtmltopdf/windows/wkhtmltopdf.exe"

5.  You should have a file structure that looks similar to this:
    - Linux:
        .libs/wkhtmltopdf/
        .libs/wkhtmltopdf/linux/
        .libs/wkhtmltopdf/linux/wkhtmltox_xxx.deb
        .libs/wkhtmltopdf/linux/wkhtmltopdf
    - MacOS:
        .libs/wkhtmltopdf/
        .libs/wkhtmltopdf/macos/
        .libs/wkhtmltopdf/macos/wkhtmltox_xxx.pkg
        .libs/wkhtmltopdf/macos/wkhtmltopdf
    - Windows:
        .libs/wkhtmltopdf/
        .libs/wkhtmltopdf/windows/
        .libs/wkhtmltopdf/windows/wkhtmltox_xxx.zip
        .libs/wkhtmltopdf/windows/wkhtmltopdf.exe

6.  PDF scripts that use this library should auto-detect the binary and use it accordingly.
