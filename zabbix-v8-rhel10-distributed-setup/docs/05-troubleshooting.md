# Troubleshooting

Common problems in this project, grouped by area. Each entry lists the symptom, the likely cause and the fix.

> **Placeholders:** Replace values in `<angle brackets>` with your own. Never paste real passwords into issues, screenshots or commits.

## 1. Where to look first

| What | Command or file |
|---|---|
| Service status | `systemctl status <service> --no-pager` |
| Service logs | `sudo journalctl -u <service> -n 50 --no-pager` |
| Zabbix server log | `/var/log/zabbix/zabbix_server.log` |
| Zabbix agent log | `/var/log/zabbix/zabbix_agentd.log` |
| Apache log | `/var/log/httpd/error_log` |
| Listening ports | `sudo ss -tlnp` |
| Firewall rules | `sudo firewall-cmd --list-all` and `--list-rich-rules` |
| SELinux denials | `sudo ausearch -m avc -ts recent` |

## 2. Network and system

| Symptom | Likely cause | Fix |
|---|---|---|
| Server unreachable after setting a static IP | IP, gateway or prefix does not match the real network | From the console, run `ip route` to see the real network. Correct the values with `nmcli connection modify`, then `nmcli connection up` |
| `ping <gateway-ip>` fails | Wrong gateway or wrong network mode of the virtual machine | Check the gateway with `ip route` on a working host. Make both VMs use the same network mode |
| `ping google.com` fails but `ping 8.8.8.8` works | DNS not set | Set `ipv4.dns` on the connection |
| Peer name does not resolve | Missing entry in `/etc/hosts` | Add the entry once on each server and test with `getent hosts <name>` |
| IP changed after reboot | Connection still on DHCP | Set `ipv4.method manual` and check `ip a` shows `noprefixroute` |
| Two servers get the same IP, or an IP changes | The static IP is inside the DHCP range of the network | Choose addresses outside the DHCP range, or move the start of the DHCP range. In VMware this is in Virtual Network Editor, DHCP Settings |
| Graphs or times are wrong | Clocks differ between servers | `timedatectl` must show the same time zone and `System clock synchronized: yes` |

## 3. Packages

| Symptom | Likely cause | Fix |
|---|---|---|
| `dnf install mysql` says no match | On RHEL 10 the package names are versioned | Use `mysql8.4-server` on the database server and `mysql8.4` for the client |
| Zabbix packages come from the wrong source | EPEL is enabled and provides Zabbix packages | Add `excludepkgs=zabbix*` under `[epel]` in `/etc/yum.repos.d/epel.repo` |
| `dnf` cannot find a Zabbix package | Zabbix repository not installed | Run the `rpm -Uvh` repository command from the install document, then `sudo dnf clean all` |

## 4. Database (db01)

| Symptom | Likely cause | Fix |
|---|---|---|
| `ERROR 2003 Can't connect to MySQL server` | MySQL not listening on the network, or the firewall blocks 3306 | On db01 check `sudo ss -tlnp \| grep 3306`. Add `bind-address=0.0.0.0` under `[mysqld]` in `/etc/my.cnf.d/mysql-server.cnf` if needed. Check the rich rule for `<zbx01-ip>` |
| `ERROR 1045 Access denied for user 'zabbix'` | Wrong password, or the host in `CREATE USER` is not the real IP of zbx01 | On db01 run `SELECT user,host FROM mysql.user;`. Recreate the user with the correct IP, or reset the password with `ALTER USER` |
| `ERROR 1419 ... SUPER privilege and binary logging is enabled` during schema import | Temporary setting not enabled | On db01 run `SET GLOBAL log_bin_trust_function_creators = 1;`, import again, then set it back to `0` |
| Schema import fails halfway | Partial import left tables behind | On db01 drop and recreate the `zabbix` database, then import again |
| `skip-networking` prevents remote logins | Option enabled in the MySQL config | Set `skip-networking=0` and restart `mysqld` |

## 5. Zabbix server and frontend (zbx01)

| Symptom | Likely cause | Fix |
|---|---|---|
| `zabbix-server` does not start | Wrong database values in the config | Check `DBHost`, `DBPort`, `DBName`, `DBUser`, `DBPassword` in `/etc/zabbix/zabbix_server.conf` and read the server log |
| Log shows `[Z3001] connection to database 'zabbix' failed` | Database unreachable, or SELinux blocks the connection | Test with `mysql -h <db01-ip> -u zabbix -p`. Then run `getsebool zabbix_can_network` and turn it on with `sudo setsebool -P zabbix_can_network on` |
| Log shows `[2002] Can't connect to server on '<db01-ip>' (115)`, or a test shows `No route to host` on port 3306 | The source IP of zbx01 is not the IP allowed in the db01 firewall rule and in the `zabbix` database user (for example a DHCP address that changed) | On zbx01 run `ip route get <db01-ip>` and check `src`. Give zbx01 a static IP, make the rule and the user use that IP, then restart `zabbix-server` |
| Setup wizard cannot connect to the database | SELinux blocks Apache from the database | `sudo setsebool -P httpd_can_network_connect_db on`, then retry |
| Setup wizard shows a failed prerequisite | PHP settings or modules missing | Read the item in the wizard. Restart `php-fpm` and `httpd` after any change |
| Browser cannot open `http://<zbx01-ip>/zabbix` | `httpd` stopped or port 80 closed | `systemctl is-active httpd` and `sudo firewall-cmd --list-services` (needs `http`) |
| Page shows "Zabbix server is not running" | Server stopped, or SELinux blocks the frontend from the server | Start `zabbix-server`. Run `sudo setsebool -P httpd_can_connect_zabbix on` |
| Config changes have no effect | Service not restarted | `sudo systemctl restart zabbix-server` |
| Forgot the Admin password | | Reset it from the database with an `UPDATE` on the `users` table, or create another super admin |

## 6. Agent and hosts

| Symptom | Likely cause | Fix |
|---|---|---|
| Host shows a red or grey ZBX icon | Server cannot reach the agent | From zbx01 run `zabbix_get -s <db01-ip> -k agent.ping`. Check the agent service, port 10050 and the firewall rule |
| Agent log: `connection from "<ip>" rejected, allowed hosts` | `Server=` does not contain the IP the server connects from | Set `Server=<zbx01-ip>` in `/etc/zabbix/zabbix_agentd.conf` and restart the agent |
| Agent log: `no active checks on server ... host [name] not found` | `Hostname=` differs from the host name in the frontend | Make both exactly the same, including case |
| No data from active checks | Port 10051 blocked on zbx01, or `ServerActive=` wrong | Open the rich rule for 10051 and check `ServerActive=<zbx01-ip>` |
| Host added but no items | No template linked | Edit the host and link `Linux by Zabbix agent` |

## 7. SELinux rule of thumb

Do not disable SELinux to fix a problem. Instead:

```bash
getenforce
sudo ausearch -m avc -ts recent
```

The denial message shows which action was blocked. Look for a matching boolean with `getsebool -a | grep zabbix` or `getsebool -a | grep httpd`, turn it on with `setsebool -P`, and test again.

## 8. Collecting information for help

Before asking for help, collect:

```bash
hostnamectl --static
ip -4 addr
systemctl is-active zabbix-server zabbix-agent httpd php-fpm mysqld
sudo tail -n 30 /var/log/zabbix/zabbix_server.log
```

Remove passwords and public IP addresses from the output before sharing it.
