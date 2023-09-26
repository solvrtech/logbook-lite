import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';
import { environment } from 'src/environments/environment';

@Injectable({
  providedIn: 'root',
})
export class PasswordService {
  constructor(protected http: HttpClient) {}

  isSetUserPasswordTokenValid(token: string): Observable<boolean> {
    return this.http.get<boolean>(`${environment.setPasswordPath}/${token}/valid`).pipe(
      map(res => {
        return res;
      })
    );
  }

  setUserPassword(token: string, email: string, password: string): Observable<boolean> {
    return this.http.post<boolean>(`${environment.setPasswordPath}/${token}`, { email, password });
  }
}
