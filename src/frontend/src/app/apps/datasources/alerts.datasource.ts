import { TableDataSource, TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { Alert } from '../interfaces/alert.interface';
import { AppAlertService } from '../services/app-alerts/app-alert.service';

export class AlertsDataSource extends TableDataSource<Alert> {
  constructor(private appAlertService: AppAlertService, private appId: string) {
    super();
  }

  loadData(option: TableLoadOption): void | Promise<any> {
    this.startLoading();

    this.appAlertService.getAppAlerts(this.appId, option).subscribe({
      next: data => {
        this.stopLoading();

        if (data != null) {
          this.rowSubject$.next(data.items);
          this.countSubject$.next(data.totalItems);
        }
      },
      error: err => {
        console.log(err);
        this.stopLoading();
      },
    });
  }

  get isPaginated(): boolean {
    return true;
  }
}
