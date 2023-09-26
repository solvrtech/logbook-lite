import { Health } from 'src/app/healths/interfaces/health.interface';
import { TableDataSource, TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { HealthService } from '../../healths/services/healths/health.service';

export class HealthDataSource extends TableDataSource<Health> {
  get isPaginated(): boolean {
    return true;
  }

  constructor(private healthService: HealthService, private appId: string) {
    super();
  }

  loadData(option: TableLoadOption): void | Promise<any> {
    this.startLoading();

    this.healthService.getHealthStatus(this.appId, option).subscribe({
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
}
