import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { map, Observable, Subject } from 'rxjs';
import { MailSetting } from 'src/app/apps/interfaces/app.interface';
import { BaseApiService } from 'src/app/shared/component/bases/base-api.service';
import { Language } from 'src/app/shared/interfaces/common.interface';
import { Response } from 'src/app/shared/interfaces/response.interface';
import { environment } from 'src/environments/environment';
import { SettingSecurity } from '../../interfaces/setting.interface';

@Injectable({
  providedIn: 'root',
})
export class SettingService extends BaseApiService {
  private static language: Language | null;
  private static securitySetting: SettingSecurity;
  private mailSetting$ = new Subject<MailSetting | null>();
  selectedIndex = 0;

  constructor(protected override http: HttpClient) {
    super(http);
  }

  get currentMailSetting() {
    return this.mailSetting$;
  }

  get currentLanguage(): Language | null {
    return SettingService.language;
  }

  get securitySetting(): SettingSecurity | null {
    return SettingService.securitySetting;
  }

  updateGeneral(applicationSubtitle: string, languagePreference: any, defaultLanguage: string): Observable<Response> {
    return this.http
      .post(`${environment.settingsPath}/general`, { applicationSubtitle, languagePreference, defaultLanguage })
      .pipe(
        map(res => {
          return res;
        })
      );
  }

  updateSecurity(request: SettingSecurity): Observable<Response> {
    return this.http.post<Response>(`${environment.settingsPath}/security`, request).pipe(
      map(res => {
        SettingService.securitySetting = request;
        return res;
      })
    );
  }

  testMailConnection(request: MailSetting): Observable<Response> {
    return this.http.post(`${environment.settingsPath}/mail/test-connection`, request).pipe(
      map(res => {
        return res;
      })
    );
  }

  validateMailConnection(tesId: string, mailSetting: MailSetting): Observable<Response> {
    return this.http.post(`${environment.settingsPath}/mail/${tesId}`, mailSetting).pipe(
      map(res => {
        return res;
      })
    );
  }

  getGeneral(): Observable<Response> {
    return this.http.get(`${environment.settingsPath}/general`).pipe(
      map(res => {
        return res;
      })
    );
  }

  getSecurity(): Observable<Response> {
    return this.http.get(`${environment.settingsPath}/security`).pipe(
      map(res => {
        return res;
      })
    );
  }

  getMailSetting() {
    this.http
      .get<Response>(`${environment.settingsPath}/mail`)
      .pipe(
        map(res => {
          if (res && res.success) {
            this.mailSetting$.next(res.data);
          } else {
            this.mailSetting$.next(null);
          }
        })
      )
      .subscribe(res => {
        return res;
      });
  }

  getSettingsAll(): Observable<Response> {
    return this.http.get<Response>(`${environment.settingsPath}/all`).pipe(
      map(res => {
        SettingService.securitySetting = res.data.securitySetting;
        SettingService.language = res.data.generalSetting;
        return res;
      })
    );
  }

  listenOnSmtpSetting(): Observable<MailSetting> {
    this.getMailSetting();
    return new Observable<MailSetting>(subcriber => {
      this.currentMailSetting.subscribe({
        next: mailSetting => {
          if (mailSetting) {
            subcriber.next(mailSetting);
          } else {
            subcriber.next();
          }
        },
        error: () => subcriber.next(),
        complete: () => subcriber.complete(),
      });
    });
  }
}
