import { HttpClient, HttpContext } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { NGX_LOADING_BAR_IGNORED } from '@ngx-loading-bar/http-client';
import { Observable, map } from 'rxjs';
import { BaseApiService } from 'src/app/shared/component/bases/base-api.service';
import { TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { PaginateData, Response } from 'src/app/shared/interfaces/response.interface';
import { environment } from 'src/environments/environment';
import { Health } from '../../interfaces/health.interface';

@Injectable({
  providedIn: 'root',
})
export class HealthService extends BaseApiService {
  constructor(http: HttpClient) {
    super(http);
  }

  getHealths(option: TableLoadOption | any): Observable<PaginateData<Health>> {
    return this.get<PaginateData<Health>>(`${environment.healthsPath}`, option);
  }

  getHealthStatus(appId: string, option: TableLoadOption | any): Observable<PaginateData<Health>> {
    return this.get<PaginateData<Health>>(`${environment.appsPath}/${appId}/health-status`, option);
  }

  healthStatusById(appId: string, healthId: number): Observable<Response> {
    return this.http
      .get<Response>(`${environment.appsPath}/${appId}/health-status/${healthId}`, {
        context: new HttpContext().set(NGX_LOADING_BAR_IGNORED, true),
      })
      .pipe(
        map(res => {
          return res;
        })
      );
  }

  getHealthStatusById(healthId: number): Observable<Response> {
    return this.http
      .get<Response>(`${environment.healthsPath}/${healthId}`, {
        context: new HttpContext().set(NGX_LOADING_BAR_IGNORED, true),
      })
      .pipe(
        map(res => {
          return res;
        })
      );
  }
}
