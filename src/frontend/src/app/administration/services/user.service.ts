import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';
import { BaseApiService } from 'src/app/shared/component/bases/base-api.service';
import { TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { FormDropdown } from 'src/app/shared/interfaces/common.interface';
import { PaginateData, Response } from 'src/app/shared/interfaces/response.interface';
import { SearchFieldOptionValue } from 'src/app/shared/interfaces/search.interface';
import { environment } from 'src/environments/environment';
import { RequestUser, User } from '../interfaces/user.interface';

@Injectable({
  providedIn: 'root',
})
export class UserService extends BaseApiService {
  constructor(protected override http: HttpClient) {
    super(http);
  }

  getUsers(option: TableLoadOption | any): Observable<PaginateData<User>> {
    return this.get<PaginateData<User>>(`${environment.usersPath}`, option);
  }

  getUserStandard() {
    return this.http.get<Response>(`${environment.usersPath}/all-standard`);
  }

  getUsersDropdown(search?: boolean): Observable<FormDropdown[] | SearchFieldOptionValue[] | any> {
    return new Observable<FormDropdown[] | SearchFieldOptionValue[]>(subscriber => {
      this.getUserStandard().subscribe({
        next: res => {
          if (res && res.success) {
            let result: FormDropdown[] | SearchFieldOptionValue[];
            if (!!search) {
              result = res.data.map((item: any) => ({
                value: item.id,
                label: item.name,
              }));
            } else {
              result = res.data.map((item: any) => ({
                value: item.id,
                description: item.name,
              }));
            }

            subscriber.next(result);
            subscriber.complete();
          } else {
            subscriber.next([]);
          }
        },
        error: () => {
          subscriber.next([]);
          subscriber.complete();
        },
      });
    });
  }

  getUsersLog(id: string): Observable<Response> {
    return this.http.get<Response>(`${environment.usersPath}/log/${id}`).pipe(
      map(res => {
        return res;
      })
    );
  }

  getCurrentUser(): Observable<Response> {
    return this.http.get<Response>(`${environment.currentUserPath}`);
  }

  getUserId(id: number): Observable<Response> {
    return this.http.get<Response>(`${environment.usersPath}/${id}`);
  }

  createUser(request: RequestUser): Observable<Response> {
    return this.http.post<Response>(`${environment.usersPath}/create`, request).pipe(
      map((res: any) => {
        return res;
      })
    );
  }

  updateUser(id: number, request: RequestUser): Observable<Response> {
    return this.http.put<Response>(`${environment.usersPath}/${id}/edit`, request).pipe(
      map(res => {
        return res;
      })
    );
  }

  isAllowedToDelete(id: number): Observable<any> {
    return this.delete(`${environment.usersPath}/${id}/allow-to-delete`);
  }

  deleteUser(id: number): Observable<any> {
    return this.delete(`${environment.usersPath}/${id}/delete`);
  }

  updateProfile(request: RequestUser): Observable<Response> {
    return this.http.put<Response>(`${environment.profilePath}/edit`, request).pipe(
      map(res => {
        return res;
      })
    );
  }
}
