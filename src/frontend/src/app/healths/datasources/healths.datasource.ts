import { TableDataSource, TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { Health } from '../interfaces/health.interface';
import { HealthService } from '../services/healths/health.service';

export class HealthsDataSource extends TableDataSource<Health> {
  constructor(private healthService: HealthService) {
    super();
  }

  loadData(option: TableLoadOption): void | Promise<any> {
    this.startLoading();

    this.healthService.getHealths(option).subscribe({
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
