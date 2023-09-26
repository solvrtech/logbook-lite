import { Injectable } from '@angular/core';
import { NavigationEnd } from '@angular/router';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class NavigationService {
  navigation$ = new BehaviorSubject<NavigationEnd | null>(null);

  constructor() {}

  onNavigationEndChanged(navigationEnd: NavigationEnd) {
    this.navigation$.next(navigationEnd);
  }
}
