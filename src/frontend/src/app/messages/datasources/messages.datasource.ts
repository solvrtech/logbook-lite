import { TableDataSource, TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { Message } from '../interfaces/message.interface';
import { MessageApiService } from '../services/message/message-api.service';

export class MessagesDataSource extends TableDataSource<Message> {
  constructor(private messageApiService: MessageApiService) {
    super();
  }

  loadData(option: TableLoadOption): void | Promise<any> {
    this.messageApiService.getMessagesTable(option).subscribe({
      next: data => {
        if (data != null) {
          this.rowSubject$.next(data.items);
          this.countSubject$.next(data.totalItems);
        }
      },
      error: err => console.log(err),
    });
  }

  get isPaginated(): boolean {
    return true;
  }
}
