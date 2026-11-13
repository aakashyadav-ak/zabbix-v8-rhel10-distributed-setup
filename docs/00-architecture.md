# Architecture

Zabbix 8 monitoring platform on RHEL 10 with the database on a separate server.

> **Placeholders:** Values in `<angle brackets>` depend on your environment. Use your own private or public IP addresses.

## 1. Overview

The platform runs on two RHEL 10 servers. The Zabbix server and the web frontend share one server, and the database runs on its own server. Keeping the database separate protects the monitoring data, lets each server be sized on its own, and is how larger environments are built.

## 2. Diagram

```
   Administrator (browser)
            |
            | 80/tcp (HTTP)
            v
  +-----------------------------+            +-----------------------------+
  | zbx01                       |   3306/tcp | db01                        |
  | Zabbix server               | ---------> | MySQL 8.4                   |
  | Apache + PHP-FPM + frontend |            | database "zabbix"           |
  | Zabbix agent                |            | minimal RHEL 10 server      |
  | <zbx01-ip>                  |            | <db01-ip>                   |
  +-----------------------------+            +-----------------------------+
            ^
            | 10051/tcp (agents and proxies, added later)
            |
     Monitored hosts (Zabbix agent, port 10050)
```

## 3. Components

| Server | Component | Role |
|---|---|---|
| zbx01 | Zabbix server 8.0 | Collects data, evaluates triggers, sends alerts |
| zbx01 | Apache HTTP Server and PHP-FPM | Serves the web frontend |
| zbx01 | Zabbix web frontend | Dashboards and configuration |
| zbx01 | Zabbix agent | Monitors the Zabbix server host itself |
| db01 | MySQL 8.4 | Stores configuration and monitoring history |

## 4. Network and ports

| From | To | Port | Purpose |
|---|---|---|---|
| Administrator | zbx01 | 80/tcp | Web frontend |
| zbx01 | db01 | 3306/tcp | Server and frontend access the database |
| Monitored hosts | zbx01 | 10051/tcp | Agent data to the server (active checks) |
| zbx01 | Monitored hosts | 10050/tcp | Server polls the agent (passive checks) |

Firewall rules are limited to the source that needs each port. On `db01`, port 3306 accepts connections from `<zbx01-ip>` only.

## 5. Platform baseline

| Item | Setting |
|---|---|
| Operating system | RHEL 10 on both servers (`db01` is a minimal install) |
| IP addressing | Static IP on both servers |
| Name resolution | IP addresses between the servers; `/etc/hosts` entries are optional |
| Time | chrony, same time zone on both servers |
| Firewall | firewalld enabled |
| SELinux | Enforcing mode, with Zabbix policy and the required settings |

## 6. Design decisions

| Decision | Reason |
|---|---|
| Separate database server | Isolates data, allows independent sizing, and reduces load on the Zabbix server |
| MySQL | Supported by Zabbix and available in RHEL 10 |
| Apache | Documented web server option for the Zabbix frontend on RHEL |
| Database user limited to one host | The `zabbix` user can connect only from `<zbx01-ip>` |
| Firewall and SELinux kept on | Security controls stay active; only required access is opened |

## 7. Implementation phases

| Phase | Document | Result |
|---|---|---|
| 0 | [Prerequisites](01-prerequisites.md) | Static IP, hostname, time, firewall, SELinux |
| 1 | [Database server](02-database-install.md) | MySQL on `db01` with the `zabbix` database |
| 2 | [Server and frontend](03-server-frontend-install.md) | Zabbix 8 running on `zbx01` |
| 3 | [Add hosts and agent](04-add-hosts-and-agent.md) | Monitored hosts reporting to Zabbix |
