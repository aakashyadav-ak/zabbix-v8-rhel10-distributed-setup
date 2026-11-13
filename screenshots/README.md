# Screenshots

The guides in `docs/` and the main `README.md` already link to the images listed below. Save each screenshot here as a PNG with the **exact file name**, and it appears in the guide automatically after you commit and push.

Before saving an image, check that it shows no passwords. Blur public IP addresses if you have any.

| File | Used in | Where | What to capture |
|---|---|---|---|
| `05-firewall-selinux.png` | docs/01 | db01 | `sudo firewall-cmd --state; sudo firewall-cmd --list-all; getenforce` |
| `06-connectivity-test.png` | docs/01 | db01 | `ping -c 2 <zbx01-ip>` |
| `07-mysql-service-running.png` | docs/02 | db01 | `systemctl status mysqld --no-pager \| head -5; mysql --version` |
| `08-zabbix-db-and-user.png` | docs/02 | db01 | `sudo mysql -u root -p -e "SHOW DATABASES; SELECT user,host FROM mysql.user WHERE user='zabbix';"` |
| `09-network-listen-and-firewall.png` | docs/02 | db01 | `sudo ss -tlnp \| grep 3306; sudo firewall-cmd --list-rich-rules` |
| `10-remote-login-from-zbx01.png` | docs/02 | zbx01 | `mysql -h <db01-ip> -u zabbix -p`, then `SHOW DATABASES;` |
| `13-server-config-db-lines.png` | docs/03 | zbx01 | `sudo grep -E '^(DBHost\|DBPort\|DBName\|DBUser)=' /etc/zabbix/zabbix_server.conf` |
| `14-selinux-booleans.png` | docs/03 | zbx01 | `getsebool httpd_can_network_connect_db zabbix_can_network httpd_can_connect_zabbix` |
| `15-services-active.png` | docs/03 | zbx01 | `systemctl is-active zabbix-server zabbix-agent httpd php-fpm` |
| `17-frontend-login.png` | docs/03 | browser | Login page at `http://<zbx01-ip>/zabbix` |
| `18-zabbix-dashboard.png` | docs/03 and README | browser | Dashboard after login |
| `19-agent-running-db01.png` | docs/04 | db01 | `systemctl is-active zabbix-agent; sudo ss -tlnp \| grep 10050` |
| `21-create-host-form.png` | docs/04 | browser | Data collection, Hosts, Create host, filled in before you click Add |
| `22-host-available.png` | docs/04 | browser | Host list with the green ZBX icon |
| `23-latest-data.png` | docs/04 | browser | Monitoring, Latest data, host `db01` |
| `24-agent-down-problem.png` | docs/04 | browser | Stop the agent on db01, wait a few minutes, then capture Monitoring, Problems. Start the agent again afterwards |

The numbering has gaps on purpose. Keep the names exactly as shown.
