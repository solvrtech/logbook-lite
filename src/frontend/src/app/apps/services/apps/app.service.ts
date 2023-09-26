import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, map } from 'rxjs';
import { BaseApiService } from 'src/app/shared/component/bases/base-api.service';
import { TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { PaginateData, Response } from 'src/app/shared/interfaces/response.interface';
import { environment } from 'src/environments/environment';
import { App, AppHealthSetting, AppStandard, RequestApp } from '../../interfaces/app.interface';

@Injectable({
  providedIn: 'root',
})
export class AppService extends BaseApiService {
  private static app: AppStandard | null;

  selected: number | null = null;

  constructor(protected override http: HttpClient) {
    super(http);
  }

  get currentApp() {
    return AppService.app;
  }

  getApps(option: TableLoadOption | any): Observable<PaginateData<App>> {
    return this.get<PaginateData<App>>(`${environment.appsPath}`, option);
  }

  getAppSearch(): Observable<Response> {
    return this.http.get<Response>(`${environment.appsPath}/get-all`).pipe(
      map(res => {
        return res;
      })
    );
  }

  createApp(request: RequestApp): Observable<Response> {
    return this.http.post<Response>(`${environment.appsPath}/create`, request).pipe(
      map(res => {
        return res;
      })
    );
  }

  getAppById(id: string): Observable<Response> {
    return this.http.get<Response>(`${environment.appsPath}/${id}`);
  }

  getAppUserStandard(id: string): Observable<Response> {
    return this.http.get<Response>(`${environment.appsPath}/${id}/standard`);
  }

  generateApiKey(id: string, api_key: string): Observable<any> {
    return this.http.post(`${environment.appsPath}/${id}/generate-api-key`, api_key);
  }

  updateApp(id: string, request: RequestApp): Observable<Response> {
    return this.http.put<Response>(`${environment.appsPath}/${id}/edit-general`, request).pipe(
      map(res => {
        return res;
      })
    );
  }

  appHealthSetting(id: string, request: AppHealthSetting): Observable<Response> {
    return this.http.post(`${environment.appsPath}/${id}/app-health-setting`, request).pipe(
      map(res => {
        return res;
      })
    );
  }

  getLogDetail(logId: string): Observable<Response> {
    return this.http.get(`${environment.logsPath}/${logId}`).pipe(
      map(res => {
        return res;
      })
    );
  }

  updateAppTeams(id: string, team: any): Observable<Response> {
    return this.http.put<Response>(`${environment.appsPath}/${id}/edit-teams`, { team }).pipe(
      map(res => {
        return res;
      })
    );
  }

  getAppLogDetail(id: string, logId: string): Observable<any> {
    return this.get(`${environment.appsPath}/${id}/log/${logId}`);
  }

  deleteApp(id: string, name: string): Observable<Response> {
    return this.http.post<Response>(`${environment.appsPath}/${id}/delete`, { name }).pipe(
      map(res => {
        return res;
      })
    );
  }

  getAllAppTypes(): Observable<Response> {
    return this.http.get(`${environment.appsPath}/get-all-type`);
  }

  fetchCurrentApp(id: string): Observable<App | null> {
    return new Observable<App | null>(subscriber => {
      this.getAppUserStandard(id).subscribe({
        next: res => {
          if (res && res.success) {
            AppService.app = res.data;
            subscriber.next(res.data);
          } else {
            subscriber.next(null);
          }
        },
        error: () => subscriber.next(null),
        complete: () => subscriber.complete(),
      });
    });
  }
}
