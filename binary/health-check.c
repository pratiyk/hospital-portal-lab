/*
 * health-check.c - Saint Mary's Clinic System Health Monitor
 * 
 * This utility checks the connectivity status of critical
 * clinic infrastructure endpoints. Run periodically via cron.
 *
 * Compiled: gcc -o health-check health-check.c
 * Deployed: /usr/bin/health-check
 *
 * Author: IT Dept - Saint Mary's Clinic
 * Date:   2015-08-12
 */

#include <stdio.h>
#include <stdlib.h>

int main(void) {
    printf("===========================================\n");
    printf("  Saint Mary's Clinic - System Health Check\n");
    printf("===========================================\n\n");

    printf("[*] Checking external connectivity...\n");
    system("curl -s -o /dev/null -w 'HTTP Status: %{http_code}\n' google.com");

    printf("[*] Health check complete.\n");
    return 0;
}
