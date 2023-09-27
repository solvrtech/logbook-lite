import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import { AppLogo } from 'src/app/apps/interfaces/app.interface';

@Injectable({
  providedIn: 'root',
})
export class LogoService {
  currentLogo$ = new BehaviorSubject<AppLogo>({});

  async setLogo(value: AppLogo) {
    this.currentLogo$.next(value);
  }

  constructor() {}
}
