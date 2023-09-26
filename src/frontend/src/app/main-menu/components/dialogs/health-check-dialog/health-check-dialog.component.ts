import { Component } from '@angular/core';
import { MatDialogRef } from '@angular/material/dialog';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-health-check-dialog',
  templateUrl: './health-check-dialog.component.html',
  styleUrls: ['./health-check-dialog.component.scss'],
})
export class HealthCheckDialogComponent {
  scoutapmLink = environment.scoutapmLink;
  constructor(private dialogRef: MatDialogRef<HealthCheckDialogComponent>) {}

  onDismiss() {
    this.dialogRef.close(false);
  }
}
