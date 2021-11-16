# Pterodactyl Audit Logs
Adds audit logs to the admin panel 
![Image](https://cdn.discordapp.com/attachments/772887124554678282/910242971134332990/Screenshot_2021-11-16_125846.png)


## Installation: 
- One Command Instalation Just run ``cd /var/www/pterodactyl && curl -Lo logs.zip https://github.com/Tdash2/Pterodactyl_audit_logs/releases/download/1.1.0/AuditLogs_V1.1.0.zip && unzip -o logs.zip && rm -rf logs.zip && yarn run build:production``  in SSH and the addon will be installed.

- Manual Instalation Download the [latest verson](https://github.com/Tdash2/Pterodactyl_audit_logs/releases/) then upload the files to your panel home directiry (It is normaly in ``/var/www/pterodactyl/``) then run build the panel (``cd /var/www/pterodactyl && yarn run build:production``)
