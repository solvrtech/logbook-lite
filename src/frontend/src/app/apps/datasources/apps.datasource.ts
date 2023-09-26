import { TableDataSource, TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { App } from '../../apps/interfaces/app.interface';
import { AppService } from '../services/apps/app.service';

export class AppsDataSource extends TableDataSource<App> {
  constructor(private appService: AppService) {
    super();
  }

  loadData(option: TableLoadOption): void | Promise<any> {
    this.startLoading();

    this.appService.getApps(option).subscribe({
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
