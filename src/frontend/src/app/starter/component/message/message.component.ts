import { Component, OnInit } from '@angular/core';

import { MatDialog } from '@angular/material/dialog';
import { takeUntil } from 'rxjs/operators';
import { Message } from 'src/app/messages/interfaces/message.interface';
import { MessageService } from 'src/app/messages/services/message/message.service';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { AutoRefreshService } from 'src/app/shared/services/auto-refresh/auto-refresh.service';
import { DialogMessageComponent } from '../dialog/dialog-message/dialog-message.component';

@Component({
  selector: 'app-message',
  templateUrl: './message.component.html',
  styleUrls: ['./message.component.scss'],
})
export class MessageComponent extends BaseComponent implements OnInit {
  constructor(
    private dialog: MatDialog,
    private autoRefreshService: AutoRefreshService,
    private messageService: MessageService
  ) {
    super();
  }

  ngOnInit(): void {
    this.messageService.getMessagesNavbar();
    this.fetchAutoRefresh();
  }

  get messages() {
    return this.messageService.currentMessages;
  }

  /**
   * @return boolean true if click load more or false otherwise
   */
  get isLoadingMore() {
    return this.messageService.isLoadingMore;
  }

  /**
   * @return boolean true if start loading or false otherwise
   */
  get isLoading() {
    return this.messageService.isLoading;
  }

  /**
   * @return boolean true if start loading auto refresh or false otherwise
   */
  get autoRefresh() {
    return this.messageService.messageApiService.autoRefresh;
  }

  /**
   * @return paginate data for the message on the navbar
   */
  get paginateData() {
    return this.messageService.paginateData;
  }

  get totalMessage() {
    return this.messageService.messageApiService.totalMessage;
  }

  /**
   * Show specific message with the given `id`, `createdAt`,`type`, and `message`
   *
   * @param {number} id
   * @param {Date} createdAt
   * @param {string} type
   * @param {message} message
   */
  onShowMessage(id: number, createdAt: Date, type: string, message: Message) {
    this.dialog
      .open(DialogMessageComponent, {
        width: '500px',
        data: {
          id: id,
          icon: this.messageIcon(type),
          createdAt: this.messageDateTime(createdAt),
          message: message,
        },
      })
      .afterOpened()
      .pipe(takeUntil(this.onDestroy$))
      .subscribe(() => this.messageService.deleteMessage(id));
  }

  /**
   * Auto refresh message
   */
  private fetchAutoRefresh() {
    this.autoRefreshService
      .getIntervalMessages()
      .pipe(takeUntil(this.onDestroy$))
      .subscribe({
        next: res => {
          if (res.enMessage) {
            if (!this.messageService.isLoading)
              this.messageService.getMessages(this.paginateData.page, this.paginateData.size, true);
          }
        },
      });
  }

  /**
   * Delete the message with the given `id` after click read
   *
   * @param {number} id
   * @param {Event} event
   * @return
   */
  onDeleteMessage(id: number, event: Event) {
    return this.messageService.deleteMessage(id, event);
  }

  /**
   * Delete all messages
   *
   * @param {Event} event
   * @return
   */
  onDeleteAllMessages(event: Event) {
    return this.messageService.deleteAllMessages(event);
  }

  /**
   * Displays the date and time of each message with the given `createdAt`
   *
   * @param {Date} createdAt
   * @return
   */
  messageDateTime(createdAt: Date) {
    return this.messageService.getDateTime(createdAt);
  }

  /**
   * Displays the icon of each message with the given `type`
   *
   * @param {string} type
   * @return
   */
  messageIcon(type: string) {
    return this.messageService.icon(type);
  }

  /**
   * Refresh message on the navbar
   *
   * @param {Event} event
   * @returns
   */
  refreshMessage(event: Event) {
    return this.messageService.refreshMessage(false, event);
  }

  /**
   * Fetch more messages
   *
   * @param {Event} event
   * @param {number} size
   * @returns
   */
  loadMore(event: Event, size: number) {
    return this.messageService.loadMore(event, size);
  }

  /**
   * Display message with substring
   *
   * @param {string} message
   * @return
   */
  setMessage(message: string) {
    let val = message.toString();
    return val.length >= 33 ? `${val.substring(0, 33)}...` : val.substring(0, 33);
  }
}
