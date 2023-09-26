import { User } from 'src/app/administration/interfaces/user.interface';
import { App, AppVersion } from 'src/app/apps/interfaces/app.interface';

export interface Log {
  id: string;
  app: App;
  dateTime: string;
  instanceId: string;
  severity: string;
  message: string;
  level: string;
  appVersion: AppVersion;
  stackTrace: [];
  browser: string;
  os: string;
  device: string;
  status: string;
  file: string;
  additional: [];
  isTeamManager: boolean;
  priority: string;
  assignee: User | any;
  tag: [];
  appType: string;
  appLogo: string;
}

export interface Comment {
  id: number;
  comment: string;
  createdAt: string;
  modified: boolean;
  userId: number;
  userName: string;
}
