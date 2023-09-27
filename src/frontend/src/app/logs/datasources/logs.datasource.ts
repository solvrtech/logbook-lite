import { TableDataSource, TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { Log } from '../interfaces/log.interface';
import { LogService } from '../services/logs/log.service';

export class LogsDataSource extends TableDataSource<Log> {
  constructor(private logService: LogService) {
    super();
  }

  loadData(option: TableLoadOption): void | Promise<any> {
    this.startLoading();

    this.logService.getLogs(option).subscribe({
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
