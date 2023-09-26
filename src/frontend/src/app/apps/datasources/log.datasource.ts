import { Log } from 'src/app/logs/interfaces/log.interface';
import { LogService } from 'src/app/logs/services/logs/log.service';
import { TableDataSource, TableLoadOption } from 'src/app/shared/component/bases/table.datasource';

export class LogDataSource extends TableDataSource<Log> {
  constructor(private appId: string, private logService: LogService) {
    super();
  }

  loadData(option: TableLoadOption): void | Promise<any> {
    this.startLoading();

    this.logService.getLog(this.appId, option).subscribe({
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
