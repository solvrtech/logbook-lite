import { ChangeDetectorRef, Component, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { MatDialogRef } from '@angular/material/dialog';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { AutoRefresh } from 'src/app/shared/interfaces/common.interface';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { AutoRefreshService } from 'src/app/shared/services/auto-refresh/auto-refresh.service';

@Component({
  selector: 'app-auto-refresh-dialog',
  templateUrl: './auto-refresh-dialog.component.html',
  styleUrls: ['./auto-refresh-dialog.component.scss'],
})
export class AutoRefreshDialogComponent extends BaseComponent implements OnInit {
  formGroup!: FormGroup;
  show!: boolean;
  autoRefresh: any;

  constructor(
    private fb: FormBuilder,
    private alerts: AlertsService,
    private dialogRef: MatDialogRef<AutoRefreshDialogComponent>,
    private autoRefreshService: AutoRefreshService,
    private cdRef: ChangeDetectorRef
  ) {
    super();
    this.autoRefresh = this.autoRefreshService.getAutoRefresh();
  }

  ngOnInit(): void {
    this.initFromGroup();
  }

  ngAfterViewChecked() {
    this.cdRef.detectChanges();
  }

  private initFromGroup() {
    this.formGroup = this.fb.group({
      enable: new FormControl(this.autoRefresh !== null ? this.autoRefresh.enable : false),
      interval: new FormControl('', []),
      enMessage: new FormControl(this.autoRefresh !== null ? this.autoRefresh.enMessage : false),
      inMessage: new FormControl('', []),
    });
  }

  onChangedEnable(toChange: boolean) {
    toChange ? this.enableInterval() : this.disableInterval();
  }

  private enableInterval() {
    const interval = this.formGroup.controls['interval'];
    interval.setValidators([Validators.required, Validators.pattern('^[0-9]*$'), Validators.min(5)]);
    interval.setValue(this.autoRefresh !== null ? this.autoRefresh.interval / 1000 : '');
    this.formGroup.updateValueAndValidity();
  }

  private disableInterval() {
    const interval = this.formGroup.controls['interval'];
    interval.setValidators(null);
    interval.setValue(this.autoRefresh !== null ? this.autoRefresh.interval / 1000 : '');
    this.formGroup.updateValueAndValidity();
  }

  onChangeMessage(toChange: boolean) {
    toChange ? this.enableMessage() : this.disableMessage();
  }

  private enableMessage() {
    const enMessage = this.formGroup.controls['inMessage'];
    enMessage.setValidators([Validators.required, Validators.pattern('^[0-9]*$'), Validators.min(10)]);
    enMessage.setValue(this.autoRefresh !== null ? this.autoRefresh.inMessage / 1000 : '');
    this.formGroup.updateValueAndValidity();
  }

  private disableMessage() {
    const interval = this.formGroup.controls['inMessage'];
    interval.setValidators(null);
    interval.setValue(this.autoRefresh !== null ? this.autoRefresh.inMessage / 1000 : '');
    this.formGroup.updateValueAndValidity();
  }

  save() {
    if (this.formGroup.valid) {
      const enMessage = this.formGroup.controls['enMessage'].value;
      const autRefresh: AutoRefresh = {
        enable: this.formGroup.controls['enable'].value,
        interval: this.formGroup.controls['interval'].value * 1000,
        enMessage: enMessage,
        inMessage: this.formGroup.controls['inMessage'].value * 1000,
      };

      this.autoRefreshService.updateAutoRefresh(autRefresh);

      this.alerts.setSuccess(_('common.msg.auto_refresh_saved'));
      this.dialogRef.close(false);
    }
  }

  onDismiss() {
    this.dialogRef.close(false);
  }
}
