import { Injectable } from '@angular/core';
import { Observable, Subject, Subscription, interval } from 'rxjs';
import { getFromLocalStorage, saveToLocalStorage } from 'src/app/shared/helpers/local-storage.helper';
import { environment } from 'src/environments/environment';
import { AutoRefresh } from '../../interfaces/common.interface';

@Injectable({
  providedIn: 'root',
})
export class AutoRefreshService {
  private autoRefreshKey = environment.autoRefreshKey;
  private autoRefresh: AutoRefresh;

  // Common
  private autoRefreshSubject$ = new Subject<AutoRefresh>();
  private intervalSubcription: Subscription | null = null;
  private subscriberCount = 0;

  // Messsage
  private autoRefreshMessage$ = new Subject<AutoRefresh>();
  private intervalMessage: Subscription | null = null;
  private subscriberMessage = 0;

  constructor() {
    this.autoRefresh = this.getAutoRefresh();
  }

  updateAutoRefresh(autoRefresh: AutoRefresh): void {
    this.saveAutoRefresh(autoRefresh);
    this.updateMessage(autoRefresh);
    this.updateCommon(autoRefresh);
  }

  private updateMessage(autoRefresh: AutoRefresh) {
    this.setStopMessageInterval();

    if (autoRefresh.enMessage) {
      this.setStartMessageInterval();
    }
  }

  private updateCommon(autoRefresh: AutoRefresh) {
    this.setStopInterval();

    if (autoRefresh.enable) {
      this.setStartInterval();
    }
  }

  getAutoRefresh(): AutoRefresh {
    const defaultAutoRefresh: AutoRefresh = {
      enable: true,
      interval: 10000,
      enMessage: true,
      inMessage: 10000,
    };
    const storedAutoRefresh = JSON.parse(getFromLocalStorage(this.autoRefreshKey));
    return storedAutoRefresh ?? defaultAutoRefresh;
  }

  getIntervalChanged(): Observable<AutoRefresh> {
    if (this.autoRefresh.enable) {
      this.setStartInterval();
    }
    return this.autoRefreshSubject$.asObservable();
  }

  private setStartInterval(): void {
    if (this.subscriberCount === 0) {
      this.intervalSubcription = interval(this.autoRefresh.interval).subscribe(() => {
        this.perfomeRefresh();
      });

      this.subscriberCount++;
    }
  }

  private setStopInterval(): void {
    if (this.subscriberCount > 0) {
      this.intervalSubcription?.unsubscribe();

      this.subscriberCount--;
    }
  }

  private perfomeRefresh(): void {
    this.autoRefreshSubject$.next(this.autoRefresh);
  }

  private saveAutoRefresh(autoRefresh: AutoRefresh): void {
    saveToLocalStorage(this.autoRefreshKey, JSON.stringify(autoRefresh));

    this.autoRefresh = autoRefresh;
  }

  // for Message auto refresh
  getIntervalMessages(): Observable<AutoRefresh> {
    if (this.autoRefresh.enMessage) {
      this.setStartMessageInterval();
    }

    return this.autoRefreshMessage$.asObservable();
  }

  private perfomeRefreshMessage(): void {
    this.autoRefreshMessage$.next(this.autoRefresh);
  }

  private setStartMessageInterval(): void {
    if (this.subscriberMessage === 0) {
      this.intervalMessage = interval(this.autoRefresh.inMessage).subscribe(() => {
        this.perfomeRefreshMessage();
      });

      this.subscriberMessage++;
    }
  }

  private setStopMessageInterval() {
    if (this.subscriberMessage > 0) {
      this.intervalMessage?.unsubscribe();

      this.subscriberMessage--;
    }
  }
}
