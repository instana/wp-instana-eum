=== Instana End User Monitoring ===
Contributors: Instana
Tags: apm, eum, monitoring, beacon
Requires at least: 4
Stable tag: trunk
License: Apache License, Version 2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0

This plugin provides an easy way to insert the Instana Web End User Monitoring (EUM) beacon to your Wordpress site.

== Description ==
This Wordpress plugin allows you to easily add the Instana Web EUM Tracing Beacon to any Wordpress installation.
The plugin will insert the required JavaScript snippet into the head section of your pages.

Note that this will require an API key for Instana and a paid subscription to Instana.

== Installation ==
Install the plugin as you would install any other plugins. 
In the Settings menu, find the entry titled Instana EUM.
Enter your API key and any additional configuration you need. 

![Configuration Dialogue](../assets/screenshot-1.jpg "Configuration Dialogue")

== Frequently Asked Questions ==
Q: What is Instana?
A: Instana is the dynamic Application Performance Management solution that automatically monitors scaled modern applications and their quality of service.

Q: Where do I find my API key?
A: In Instana's settings dialog, you can select the End-User Monitoring menu item. In this dialog, keys can be generated for monitored applications.

Q: Are you anonymizing IPs?
Yes, IPs are anonymized. Specifically, the last octet of IPv4 addresses and the last 80 bits of IPv6 addresses are set to zeros.

Q: We have Content Security Policy, is there anything we need to do?
A: The Instana JS agent is asynchronously loaded from eum.instana.io and can be loaded via HTTP and HTTPS. Please ensure that loading scripts from this domain is possible and that both GET and POST requests are allowed.
