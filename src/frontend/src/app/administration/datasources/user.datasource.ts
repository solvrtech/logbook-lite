import { TableDataSource, TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { User } from '../interfaces/user.interface';
import { UserService } from '../services/user.service';

export class UserDataSource extends TableDataSource<User> {
  constructor(private userService: UserService) {
    super();
  }

  get isPaginated(): boolean {
    return true;
  }

  loadData(option: TableLoadOption): void | Promise<any> {
    this.startLoading();

    this.userService.getUsers(option).subscribe({
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
