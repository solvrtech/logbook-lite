import { Component, OnInit } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { BehaviorSubject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
import { RoleService } from 'src/app/administration/services/role.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { TableColumns, TableRowMenuItems } from 'src/app/shared/interfaces/table.interface';
import { DialogMessageComponent } from 'src/app/starter/component/dialog/dialog-message/dialog-message.component';
import { AppRole } from 'src/app/starter/data/permissions.data';
import { MessagesDataSource } from '../../datasources/messages.datasource';
import { Ids, Message } from '../../interfaces/message.interface';
import { MessageApiService } from '../../services/message/message-api.service';
import { MessageService } from '../../services/message/message.service';

@Component({
  selector: 'app-messages',
  templateUrl: './messages.component.html',
  styleUrls: ['./messages.component.scss'],
})
export class MessagesComponent extends BaseSecurePageComponent implements OnInit {
  dataSource!: MessagesDataSource;
  columns$ = new BehaviorSubject<TableColumns>([]);
  rowMenus$ = new BehaviorSubject<TableRowMenuItems>([]);
  messages: Message[] = [];
  selected = false;
  ids: Ids | any = [];
  checked = false;

  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD];
  }

  constructor(
    override roleService: RoleService,
    private messageApiService: MessageApiService,
    private dialog: MatDialog,
    private messageService: MessageService
  ) {
    super(roleService);
    this.dataSource = new MessagesDataSource(this.messageApiService);
  }

  onInit(): void {
    this.pageState.state = 'loaded';
    this.setTableColums();
    this.setTableRowMenus();
  }

  private setTableColums() {
    this.columns$.next([
      {
        name: 'common.datetime',
        key: 'createdAt',
        getValue: (data: Message) => this.messageService.getDateTime(data.createdAt),
      },
      {
        name: 'common.name',
        key: 'app',
        getValue: (data: Message) => data.app.name,
        logo: (data: Message) => data.app.appLogo,
        icon: (data: Message) => this.messageService.icon(data.app.type),
      },
      {
        name: 'common.message',
        key: 'message',
        getValue: (data: Message) => {
          let val = data.message.toString();
          return val.length >= 100 ? `${val.substring(0, 100)}...` : val.substring(0, 100);
        },
      },
    ]);
  }

  private setTableRowMenus() {
    this.rowMenus$.next([
      {
        label: 'common.detail',
        icon: 'info',
        action: ($e, row) => this.onDetailRow($e, row),
        hasContextMenu: true,
        hide: () => !this.isCurrentUserHas([AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD]),
      },
    ]);
  }

  private onDetailRow(event: MouseEvent, row: Message) {
    this.dialog
      .open(DialogMessageComponent, {
        width: '500px',
        data: {
          id: row.id,
          icon: this.messageService.icon(row.app.type),
          createdAt: this.messageService.getDateTime(row.createdAt),
          message: row,
        },
      })
      .afterOpened()
      .pipe(takeUntil(this.onDestroy$))
      .subscribe(() => {
        this.messageApiService.deleteMessage(row.id).subscribe({
          next: res => {
            if (res && res.success) {
              this.dataSource.refresh();
              this.messageService.refreshMessage();
            }
          },
          error: err => console.error(err),
        });
      });
  }

  onMessageSelected(row: Message[] | any) {
    const message: Message[] = row;
    if (this.ids.length > 0) {
      message.forEach(res => {
        const isDuplicate = this.ids.find((data: any) => data === res.id);

        if (!isDuplicate) {
          this.messages.push(res);
        }
      });
    } else {
      this.messages = row;
    }

    this.messages.length > 0 ? (this.selected = true) : (this.selected = false);
  }

  deleteAllMessages() {
    if (this.messages !== null) {
      const ids: any[] = [];

      this.messages.forEach(res => {
        ids.push(res.id);
        this.ids = ids;
      });

      this.messageApiService.deleteMessageTable(this.ids).subscribe({
        next: res => {
          if (res && res.success) {
            this.messages = [];
            this.selected = false;
            this.dataSource.refresh();
            this.messageService.refreshMessage();
          }
        },
      });
    }
  }
}
