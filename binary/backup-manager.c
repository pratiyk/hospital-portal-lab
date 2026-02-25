/*
 * backup-manager.c - Saint Mary's Clinic Database Backup Utility
 *
 * This tool performs nightly database backups.
 * Uses full paths for security (safe SUID binary - decoy).
 *
 * Author: IT Dept - Saint Mary's Clinic
 * Date:   2016-03-22
 */

#include <stdio.h>
#include <stdlib.h>

int main(void) {
    printf("==========================================\n");
    printf("  Saint Mary's Clinic - Backup Manager\n");
    printf("==========================================\n\n");

    printf("[*] Starting database backup...\n");
    // Uses full path - NOT vulnerable to PATH hijacking
    system("/usr/bin/mysqldump --version 2>/dev/null || echo 'Backup service unavailable'");

    printf("[*] Verifying backup integrity...\n");
    system("/usr/bin/md5sum --version 2>/dev/null || echo 'Verification unavailable'");

    printf("[*] Backup process complete.\n");
    return 0;
}
