import { HttpClient, HttpContext } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { NGX_LOADING_BAR_IGNORED } from '@ngx-loading-bar/http-client';
import { BehaviorSubject, Observable, map } from 'rxjs';
import { BaseApiService } from 'src/app/shared/component/bases/base-api.service';
import { TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { PaginateData, Response } from 'src/app/shared/interfaces/response.interface';
import { environment } from 'src/environments/environment';
import { Comment, Log } from '../../interfaces/log.interface';

@Injectable({
  providedIn: 'root',
})
export class LogService extends BaseApiService {
  private comment$ = new BehaviorSubject<Comment[]>([]);
  comments = this.comment$;

  constructor(protected override http: HttpClient) {
    super(http);
  }

  getLogs(option: TableLoadOption | any): Observable<PaginateData<Log>> {
    return this.get<PaginateData<Log>>(`${environment.logsPath}`, option);
  }

  getLog(appId: string, option: TableLoadOption | any): Observable<PaginateData<Log>> {
    return this.get<PaginateData<Log>>(`${environment.appsPath}/${appId}/log`, option);
  }

  getLogDetail(logId: string): Observable<Response> {
    return this.http.get(`${environment.logsPath}/${logId}`).pipe(
      map(res => {
        return res;
      })
    );
  }

  updateStatus(id: string, status: string): Observable<Response> {
    return this.http
      .post<Response>(
        `${environment.logsPath}/${id}/action-status`,
        { status },
        { context: new HttpContext().set(NGX_LOADING_BAR_IGNORED, true) }
      )
      .pipe(
        map(res => {
          return res;
        })
      );
  }

  updatePriority(id: string, priority: string): Observable<Response> {
    return this.http
      .post<Response>(
        `${environment.logsPath}/${id}/action-priority`,
        { priority },
        { context: new HttpContext().set(NGX_LOADING_BAR_IGNORED, true) }
      )
      .pipe(
        map(res => {
          return res;
        })
      );
  }

  updateAssignee(id: string, assignee: string): Observable<Response> {
    return this.http
      .post<Response>(
        `${environment.logsPath}/${id}/action-assignee`,
        { assignee },
        { context: new HttpContext().set(NGX_LOADING_BAR_IGNORED, true) }
      )
      .pipe(
        map(res => {
          return res;
        })
      );
  }

  updateTags(id: string, tag: string): Observable<Response> {
    return this.http
      .post<Response>(
        `${environment.logsPath}/${id}/action-tag`,
        { tag },
        { context: new HttpContext().set(NGX_LOADING_BAR_IGNORED, true) }
      )
      .pipe(
        map(res => {
          return res;
        })
      );
  }

  logComment(id: string, comment: string): Observable<Response> {
    return this.http
      .post<Response>(
        `${environment.logsPath}/${id}/comment`,
        { comment },
        { context: new HttpContext().set(NGX_LOADING_BAR_IGNORED, true) }
      )
      .pipe(
        map(res => {
          return res;
        })
      );
  }

  getComment(id: string) {
    this.http
      .get<Response>(`${environment.logsPath}/${id}`, { context: new HttpContext().set(NGX_LOADING_BAR_IGNORED, true) })
      .pipe(
        map(res => {
          if (res && res.success) {
            this.comments.next(res.data.comments);
          }
        })
      )
      .subscribe(res => {
        return res;
      });
  }

  editComment(id: string, commentId: number, comment: string): Observable<Response> {
    return this.http
      .put<Response>(
        `${environment.logsPath}/${id}/comment/${commentId}/edit`,
        { comment },
        { context: new HttpContext().set(NGX_LOADING_BAR_IGNORED, true) }
      )
      .pipe(
        map(res => {
          return res;
        })
      );
  }

  deleteComment(id: string, commentId: number): Observable<Response> {
    return this.http
      .delete<Response>(`${environment.logsPath}/${id}/comment/${commentId}/delete`, {
        context: new HttpContext().set(NGX_LOADING_BAR_IGNORED, true),
      })
      .pipe(
        map(res => {
          return res;
        })
      );
  }
}
