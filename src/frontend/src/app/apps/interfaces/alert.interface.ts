import { Specific } from './app.interface';

export interface Alert {
  name: string;
  active: boolean;
  source: string;
  lastNotified: string;
}

export interface AppAlert extends Alert {
  id: number;
  notifyTo: string;
  restrictNotify: boolean;
  notifyLimit: number;
  config: ConfigLog | ConfigHealth | any;
}

export interface ConfigLog {
  level: any;
  manyFailures: number;
  duration: number;
  message: string;
  stackTrace: string;
  browser: string;
  os: string;
  device: string;
  additional: string;
}

export interface ConfigHealth {
  specific: Specific;
  manyFailures: number;
}

export interface CreateAlert {
  name: string;
  active: boolean;
  source: string;
  notifyTo: string;
  restrictNotify: boolean;
  notifyLimit: number;
  config: ConfigLog | ConfigHealth | any;
}
