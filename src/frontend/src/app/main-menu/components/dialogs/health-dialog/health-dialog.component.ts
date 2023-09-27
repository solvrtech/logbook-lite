import { Component, Inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { Health, HealthCheck } from 'src/app/healths/interfaces/health.interface';
import { HealthService } from 'src/app/healths/services/healths/health.service';
import { formatDateTime } from 'src/app/shared/helpers/date.helper';

export interface HealthData {
  appId: string;
  healthId: number;
}

@Component({
  selector: 'app-health-dialog',
  templateUrl: './health-dialog.component.html',
  styleUrls: ['./health-dialog.component.scss'],
})
export class HealthDialogComponent {
  healthStatus!: Health;
  createdAt!: string;
  healthCheck: HealthCheck[] = [];
  databaseClick: boolean = true;
  databaseVisible: boolean = false;

  constructor(
    private healthService: HealthService,
    private settingService: SettingService,
    private dialogRef: MatDialogRef<HealthDialogComponent>,
    @Inject(MAT_DIALOG_DATA) data: HealthData
  ) {
    if (data.appId && data.healthId) {
      this.healthDetail(data.appId, data.healthId);
    } else {
      this.detailByHealthId(data.healthId);
    }
  }

  /**
   * Fetch app health status detail data with the given `app id` and `health id`
   *
   * @param {string} appId : app id
   * @param {number} healthId : health id
   */
  private healthDetail(appId: string, healthId: number) {
    this.healthService.healthStatusById(appId, healthId).subscribe({
      next: res => {
        if (res && res.success) {
          this.healthStatus = res.data;
          this.healthCheck = this.healthStatus.healthCheck;
          this.createdAt = formatDateTime({ date: this.healthStatus.createdAt }, this.settingService);
        }
      },
    });
  }

  /**
   * Fetch app health status detail data with the given `health id`
   *
   * @param {number} healthId : health id
   */
  private detailByHealthId(healthId: number) {
    this.healthService.getHealthStatusById(healthId).subscribe({
      next: res => {
        if (res && res.success) {
          this.healthStatus = res.data;
          this.healthCheck = this.healthStatus.healthCheck;
          this.createdAt = formatDateTime({ date: this.healthStatus.createdAt }, this.settingService);
        }
      },
    });
  }

  onDismiss() {
    this.dialogRef.close(false);
  }

  onClick() {
    this.databaseClick = !this.databaseClick;
    this.databaseVisible = !this.databaseVisible;
  }

  databaseLength(data: object): number {
    let databaseLength = Object.keys(data).length;
    return databaseLength;
  }

  cpuReformatValue(data: number | any) {
    return parseFloat(data).toFixed(2);
  }
}
