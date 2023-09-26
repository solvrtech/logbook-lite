import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, map, Observable, of } from 'rxjs';
import { TwoFactor, User } from 'src/app/administration/interfaces/user.interface';
import { UserService } from 'src/app/administration/services/user.service';
import { AuthResponse } from 'src/app/shared/interfaces/auth.interface';
import { Response } from 'src/app/shared/interfaces/response.interface';
import { environment } from 'src/environments/environment';

const apiUrl = environment.apiUrl;

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  /** holds the data of the currently active user */
  private static user: User | null = null;

  /** holds the data of the currently active two-factor authenticator */
  private static twoFactor: TwoFactor | null;

  /** holds waiting time to re-login */
  private static waitingTimeInMinutes: string | null = null;

  /** holds expired date of currently active access token in cookie */
  private static accessTokenExpiredDate: Date | null = null;

  /** holds expired date of the refresh token in cookie */
  private static refreshTokenExpiredDate: Date | null = null;

  /** timeout id for refresh access token function */
  private refreshTokenTimeout: any;

  constructor(protected http: HttpClient, public router: Router, private userService: UserService) {}

  /**
   * Returns currently active user
   */
  get currentUser(): User | null {
    return AuthService.user;
  }

  /**
   * Returns currently active two-factor authenticator
   */
  get currentTwoFactor(): TwoFactor | null {
    return AuthService.twoFactor;
  }

  /**
   * Check how many minutes it takes to wait for user login
   * @return Observable {string | null}
   */
  get maxMinutes(): string | null {
    return AuthService.waitingTimeInMinutes;
  }

  /**
   * Helper to fetch currently active user based on current access token cookie being set
   * @param callback optional callback function that will be run after getting user's info
   */
  get fetchCurrentUser(): Observable<User | null> {
    return new Observable<User | null>(subscriber => {
      this.userService.getCurrentUser().subscribe({
        next: res => {
          if (res && res.success) {
            subscriber.next(res.data);
            AuthService.user = res.data;
          } else {
            subscriber.next(null);
          }
        },
        error: err => {
          console.log(err);
          subscriber.next(null);
        },
        complete: () => {
          subscriber.complete();
        },
      });
    });
  }

  /**
   * Authenticates a user based on the given username and password
   * @param username username of the user
   * @param password password of the user
   * @return Observable of boolean (true if signed in just fine, or false otherwise)
   */
  login(email: string, password: string): Observable<boolean> {
    return new Observable<boolean>(subscriber => {
      this.http
        .post(`${apiUrl}/${environment.authenticationPath}`, {
          email,
          password,
        })
        .subscribe({
          next: (res: any) => {
            if (res && res.success) {
              if (res.data) {
                subscriber.next(true);
                AuthService.twoFactor = res.data;
              } else {
                this.setTokensExpiredDate(res);
                this.startRefreshTokenTimer();

                AuthService.twoFactor = null;
                subscriber.next(true);
              }
            } else {
              AuthService.waitingTimeInMinutes = res.data.WaitingTimeInMinutes ?? null;
              subscriber.next(false);
            }
          },
          error: err => {
            console.log(err);
            subscriber.next(false);
          },
          complete: () => {
            subscriber.complete();
          },
        });
    });
  }

  /**
   * Updates access and refresh tokens as found on the given response
   * @param response AuthResponse containing validity date of access and refresh tokens
   */
  private setTokensExpiredDate(response: AuthResponse) {
    if (response) {
      AuthService.accessTokenExpiredDate = new Date(response.accessTokenExpiration * 1000);
      AuthService.refreshTokenExpiredDate = new Date(response.refreshTokenExpiration * 1000);
    }
  }

  /**
   * Refreshes the shortlived cookie-based access token, by using the refresh token
   */
  refreshToken(): Observable<any> {
    return this.http.post<AuthResponse>(`${apiUrl}/${environment.refreshTokenPath}`, {}).pipe(
      map(res => {
        if (res && res.success) {
          this.setTokensExpiredDate(res);
          this.startRefreshTokenTimer();
        }
      }),
      catchError(err => of([]))
    );
  }

  /**
   * Initiate timeout function for refreshing cookie-based `access` (and also `refresh`) token
   */
  private startRefreshTokenTimer() {
    this.stopRefreshTokenTimer();

    // set a timeout to refresh the access token one minute before it expires
    const timeout =
      AuthService.accessTokenExpiredDate != null
        ? AuthService.accessTokenExpiredDate.getTime() - Date.now() - 60 * 1000
        : 3600000; // or in 1 hour if ACCESS_TOKEN_EXPIRED_DATE is not found
    this.refreshTokenTimeout = setTimeout(() => this.refreshToken().subscribe(), timeout);
  }

  /**
   * Clear timeout id for `startRefreshTokenTimer()`
   */
  private stopRefreshTokenTimer() {
    clearTimeout(this.refreshTokenTimeout);
  }

  /**
   * Clears expired date of access and refresh tokens
   */
  private clearTokensExpiredDate() {
    AuthService.accessTokenExpiredDate = null;
    AuthService.refreshTokenExpiredDate = null;
  }

  /**
   * Logs out currently authenticated user based on the current access token cookie being set
   */
  logout(callback?: any) {
    this.http.post<AuthResponse>(`${apiUrl}/${environment.logoutPath}`, {}).subscribe(res => {
      if (res && res.success) {
        if (typeof callback === 'function') {
          callback();
        }

        AuthService.user = null;
        this.clearTokensExpiredDate();
        this.stopRefreshTokenTimer();
        this.router.navigate(['/login']);
      }
    });
  }

  /**
   * Send a reset password request from the given email
   */
  resetPassword(email: string): Observable<any> {
    return this.http.post(`${apiUrl}/${environment.resetPasswordPath}`, { email });
  }

  /**
   * Authenticates a token based on the given email and otp token
   *
   * @param {String} email
   * @param {string} otpToken
   * @return boolean Observable
   */
  authenticateMfaToken(email: string, otpToken: string): Observable<boolean> {
    return new Observable<boolean>(subscriber => {
      this.http
        .post<AuthResponse>(`${apiUrl}/${environment.authPath}/mfa/check`, {
          email,
          otpToken,
        })
        .subscribe({
          next: res => {
            if (res && res.success) {
              this.setTokensExpiredDate(res);
              this.startRefreshTokenTimer();

              subscriber.next(true);
            } else {
              AuthService.waitingTimeInMinutes = res.data?.WaitingTimeInMinutes ?? null;
              subscriber.next(false);
            }
          },
          error: err => {
            console.log(err);
            subscriber.next(false);
          },
          complete: () => {
            subscriber.complete();
          },
        });
    });
  }

  /**
   * Authenticates MFA token based on the given email and recoveryKey
   *
   * @param {string} email
   * @param {string} recoveryKey
   * @return boolean Observable
   */
  authenticateMfaByRecoveryKey(email: string, recoveryKey: string): Observable<boolean> {
    return new Observable<boolean>(subscriber => {
      this.http
        .post<AuthResponse>(`${apiUrl}/${environment.authPath}/mfa/recovery-check`, {
          email,
          recoveryKey,
        })
        .subscribe({
          next: res => {
            if (res && res.success) {
              this.setTokensExpiredDate(res);
              this.startRefreshTokenTimer();
              subscriber.next(true);
            } else {
              AuthService.waitingTimeInMinutes = res.data?.WaitingTimeInMinutes ?? null;
              subscriber.next(false);
            }
          },
          error: err => {
            console.log(err);
            subscriber.next(false);
          },
          complete: () => {
            subscriber.complete();
          },
        });
    });
  }

  /**
   * Resends the MFA token to the user's email
   *
   * @param {string} email
   * @return boolean Observable
   */
  resendMfaToken(email: string): Observable<boolean> {
    return new Observable<boolean>(subcriber => {
      this.http.post<Response>(`${apiUrl}/${environment.authPath}/mfa/resend`, { email }).subscribe({
        next: res => {
          if (res && res.success) {
            subcriber.next(true);
          } else {
            AuthService.waitingTimeInMinutes = res?.data.waitingTimeInMinutes;
            subcriber.next(false);
          }
        },
        error: err => console.log(err),
        complete: () => subcriber.complete(),
      });
    });
  }
}
