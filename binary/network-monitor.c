/*
 * network-monitor.c - Saint Mary's Clinic Network Diagnostics
 *
 * Monitors network connectivity to critical endpoints.
 * Uses full paths (safe - decoy).
 *
 * Author: IT Dept - Saint Mary's Clinic
 * Date:   2016-05-10
 */

#include <stdio.h>
#include <stdlib.h>

int main(void) {
    printf("==========================================\n");
    printf("  Saint Mary's Clinic - Network Monitor\n");
    printf("==========================================\n\n");

    printf("[*] Checking DNS resolution...\n");
    // Uses full path - NOT vulnerable
    system("/usr/bin/host google.com 2>/dev/null || echo 'DNS check failed'");

    printf("[*] Checking gateway ping...\n");
    system("/bin/ping -c 1 127.0.0.1 > /dev/null 2>&1 && echo 'Gateway reachable' || echo 'Gateway unreachable'");

    printf("[*] Network diagnostics complete.\n");
    return 0;
}
