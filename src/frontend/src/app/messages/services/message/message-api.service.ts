import { HttpClient, HttpContext, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { NGX_LOADING_BAR_IGNORED } from '@ngx-loading-bar/http-client';
import { BehaviorSubject, Observable, map } from 'rxjs';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { BaseApiService } from 'src/app/shared/component/bases/base-api.service';
import { TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { PaginateData, Response } from 'src/app/shared/interfaces/response.interface';
import { environment } from 'src/environments/environment';
import { Ids, Message } from '../../interfaces/message.interface';

@Injectable({
  providedIn: 'root',
})
export class MessageApiService extends BaseApiService {
  private messages$ = new BehaviorSubject<[]>([]);

  private static totalMessages: number;
  private static isAutoRefresh: boolean = false;
  private static isLoading: boolean = false;

  constructor(http: HttpClient, public settingService: SettingService) {
    super(http);
  }

  get totalMessage() {
    return MessageApiService.totalMessages;
  }

  get autoRefresh(): boolean {
    return MessageApiService.isAutoRefresh;
  }

  get loading(): boolean {
    return MessageApiService.isLoading;
  }

  get messages(): Observable<Message[]> {
    return this.messages$.asObservable();
  }

  /**
   * Get API data messages
   *
   * @param {number} page
   * @param {number} size
   * @param {boolean} refresh
   */
  getMessages(page?: number, size?: number, refresh: boolean = false) {
    if (refresh) MessageApiService.isAutoRefresh = true;
    MessageApiService.isLoading = true;
    const params = new HttpParams().set('page', page ?? 1).set('size', size ?? 25);
    this.http
      .get<Response>(`${environment.messagePath}`, {
        params,
        context: new HttpContext().set(NGX_LOADING_BAR_IGNORED, true),
      })
      .pipe(
        map(res => {
          setTimeout(() => {
            MessageApiService.isAutoRefresh = false;
          }, 1000);

          if (res && res.success) {
            this.messages$.next(res.data);
            MessageApiService.totalMessages = res.data.totalItems;
          }
        })
      )
      .subscribe(res => {
        setTimeout(() => {
          MessageApiService.isAutoRefresh = false;
          MessageApiService.isLoading = true;
        }, 1000);
        return res;
      });
  }

  /**
   * API delete data Message when the given `notificationId`
   *
   * @param {number} notificationId
   * @return Observable of Response
   */
  deleteMessage(notificationId: number): Observable<Response> {
    return this.http
      .delete<Response>(`${environment.messagePath}/${notificationId}/delete`, {
        context: new HttpContext().set(NGX_LOADING_BAR_IGNORED, true),
      })
      .pipe(
        map(res => {
          return res;
        })
      );
  }

  /**
   * API delete all Messages
   *
   * @return Observable of Response
   */
  deleteAllMessages(): Observable<Response> {
    return this.http
      .delete<Response>(`${environment.messagePath}/delete-all`, {
        context: new HttpContext().set(NGX_LOADING_BAR_IGNORED, true),
      })
      .pipe(
        map(res => {
          return res;
        })
      );
  }

  /**
   * Get API Message by `id`
   *
   * @param {number} id
   * @return
   */
  getMessageById(id: number): Observable<Response> {
    return this.http.get<Response>(`${environment.messagePath}/${id}`).pipe(
      map(res => {
        return res;
      })
    );
  }

  /**
   * Get API for the Message table
   *
   * @param {TableLoadOption | any} option
   * @return Observable of paginate data Message
   */
  getMessagesTable(option: TableLoadOption | any): Observable<PaginateData<Message>> {
    return this.get<PaginateData<Message>>(`${environment.messagePath}`, option);
  }

  /**
   * API Delete data for the Message table with the given `Ids`
   *
   * @param {Ids} ids
   * @return
   */
  deleteMessageTable(ids: Ids): Observable<Response> {
    return this.http.post<Response>(`${environment.messagePath}/bulk-delete`, { ids }).pipe(
      map(res => {
        return res;
      })
    );
  }
}
