import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';
import { BaseApiService } from 'src/app/shared/component/bases/base-api.service';
import { TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { PaginateData, Response } from 'src/app/shared/interfaces/response.interface';
import { environment } from 'src/environments/environment';
import { Alert, CreateAlert } from '../../interfaces/alert.interface';

@Injectable({
  providedIn: 'root',
})
export class AppAlertService extends BaseApiService {
  constructor(protected override http: HttpClient) {
    super(http);
  }

  getAppAlerts(appId: string, option: TableLoadOption | any): Observable<PaginateData<Alert>> {
    return this.get<PaginateData<Alert>>(`${environment.appsPath}/${appId}/alert`, option);
  }

  createAppAlert(appId: string, request: CreateAlert): Observable<Response> {
    return this.http.post<Response>(`${environment.appsPath}/${appId}/alert/create`, request).pipe(
      map(res => {
        return res;
      })
    );
  }

  getAppAlertById(appId: string, alertId: number): Observable<any> {
    return this.get(`${environment.appsPath}/${appId}/alert/${alertId}`);
  }

  updateAppAlert(appId: string, alertId: number, request: CreateAlert): Observable<Response> {
    return this.http.put<Response>(`${environment.appsPath}/${appId}/alert/${alertId}/edit`, request).pipe(
      map(res => {
        return res;
      })
    );
  }

  deleteAppAlert(appId: string, alertId: number): Observable<Response> {
    return this.http.delete(`${environment.appsPath}/${appId}/alert/${alertId}/delete`).pipe(
      map(res => {
        return res;
      })
    );
  }
}
