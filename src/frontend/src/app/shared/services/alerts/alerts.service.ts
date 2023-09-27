import { Injectable } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { TranslateService } from '@ngx-translate/core';
import { lastValueFrom } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class AlertsService {
  constructor(private snackBar: MatSnackBar, private translateService: TranslateService) {}

  setError(messageToken: string) {
    this.setAlert(messageToken, 'error-alert');
  }

  setSuccess(messageToken: string) {
    this.setAlert(messageToken, 'success-alert');
  }

  setWarning(messageToken: string) {
    this.setAlert(messageToken, 'warning-alert');
  }

  setErrorWithData(messageToken: string, data: string) {
    this.setAlertWithData(messageToken, data, 'error-alert');
  }

  setSuccessWithData(messageToken: string, data: string) {
    this.setAlertWithData(messageToken, data, 'success-alert');
  }

  setWarningWithData(messageToken: string, data: string) {
    this.setAlertWithData(messageToken, data, 'warning-alert');
  }

  private async setAlert(messageToken: string, colorClass: string) {
    const close = await lastValueFrom(this.translateService.get(_('common.close')));
    const message = await lastValueFrom(this.translateService.get(messageToken));

    this.snackBar.open(message, close, {
      panelClass: [colorClass],
      duration: 7000,
    });
  }

  private async setAlertWithData(messageToken: string, data: string, colorClass: string) {
    const close = await lastValueFrom(this.translateService.get(_('common.close')));
    const message = await this.translateService.instant(messageToken, {
      data: data,
    });

    this.snackBar.open(message, close, {
      panelClass: [colorClass],
      duration: 7000,
    });
  }
}
