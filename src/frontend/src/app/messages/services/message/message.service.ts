import { Injectable } from '@angular/core';
import { isArray } from 'lodash';
import { BehaviorSubject, Observable } from 'rxjs';
import { Message } from 'src/app/messages/interfaces/message.interface';

import { AppSharedService } from 'src/app/main-menu/services/app-shared/app-shared.service';
import { formatDateTime } from 'src/app/shared/helpers/date.helper';
import { PaginateData } from 'src/app/shared/interfaces/response.interface';
import { MessageApiService } from './message-api.service';

@Injectable({
  providedIn: 'root',
})
export class MessageService {
  // For Message
  isLoading = false;
  isLoadingMore = false;
  paginateData!: PaginateData<Message>;
  columnMessages: Message[] | any = [];
  columnMessages$ = new BehaviorSubject<[]>([]);

  constructor(
    public messageApiService: MessageApiService,

    private appSharedService: AppSharedService
  ) {}

  getMessages(page?: number, size?: number, refresh?: boolean) {
    return this.messageApiService.getMessages(page, size, refresh);
  }

  /**
   * Refresh data message
   *
   * @param {boolean} refresh
   * @param {Event} event
   */
  refreshMessage(refresh?: boolean, event?: Event) {
    this.isLoading = refresh ? false : this.messageApiService.loading;
    event ? event.stopPropagation() : '';

    setTimeout(() => {
      this.columnMessages = [];
      this.columnMessages$.next(this.columnMessages);
      this.getMessages(1, 25, refresh);
      this.isLoading = false;
    }, 1000);
  }

  /**
   * Get data messages with paginated
   */
  get currentMessages(): Observable<Array<Message>> {
    return this.columnMessages$.asObservable();
  }

  /**
   * Show messages on the navbar
   */
  getMessagesNavbar() {
    this.refreshMessage();

    this.messageApiService.messages.subscribe((res: any) => {
      if (res) {
        this.paginateData = res;

        if (this.columnMessages.length !== 0) {
          const messageCheck: Array<Message> = this.columnMessages;
          const newMessage: Array<Message> = res.items;

          newMessage.forEach(item => {
            const isDuplicate = messageCheck.find(check => check.id === item.id);
            if (!isDuplicate) {
              this.columnMessages.push(item);
            }
          });
        } else {
          this.columnMessages = res.items ?? [];
        }

        if (isArray(this.columnMessages)) {
          this.columnMessages.sort((a, b) => b.id - a.id);
        }

        this.columnMessages$.next(this.columnMessages);
      }
    });
  }

  /**
   * Delete specific messages with the given `id`
   *
   * @param {number} id
   * @param {Event} event
   */
  deleteMessage(id: number, event?: Event) {
    this.isLoading = true;
    event ? event.stopPropagation() : null;

    this.messageApiService.deleteMessage(id).subscribe({
      next: res => {
        if (res && res.success) {
          const index = this.columnMessages.findIndex((data: any) => data.id === id);
          this.columnMessages.splice(index, 1);
          this.getMessages(this.paginateData.page, this.paginateData.size);

          setTimeout(() => {
            this.isLoading = false;
          }, 1000);
        } else {
          this.isLoading = false;
        }
      },
      error: err => {
        this.isLoading = false;
      },
    });
  }

  /**
   * Load more to see a lot of messages
   *
   * @param {Event} event
   * @param {number} size
   */
  loadMore(event: Event, size: number) {
    event.stopPropagation();
    this.isLoadingMore = this.messageApiService.loading;

    setTimeout(() => {
      this.getMessages(1, 25 + size);
      this.isLoadingMore = false;
    }, 1000);
  }

  /**
   * Delete all messages
   *
   * @param {Event} event
   */
  deleteAllMessages(event: Event) {
    this.isLoading = true;
    event.stopPropagation();

    this.messageApiService.deleteAllMessages().subscribe({
      next: res => {
        if (res && res.success) {
          this.columnMessages = [];
          this.columnMessages$.next(this.columnMessages);
          this.getMessages(1, this.paginateData.size);

          setTimeout(() => {
            this.isLoading = false;
          }, 1000);
        } else {
          this.isLoading = false;
        }
      },
      error: err => {
        this.isLoading = false;
      },
    });
  }

  /**
   * Returns formatted date for message
   *
   * @param {Date} createdAt
   */
  getDateTime(createdAt: Date) {
    return formatDateTime({ date: createdAt }, this.messageApiService.settingService);
  }

  /**
   * Return icon when value logo null for message
   *
   * @param  {string} type
   * @returns {string}
   */
  icon(type: string): string {
    return this.appSharedService.getAppIcon(type);
  }
}
