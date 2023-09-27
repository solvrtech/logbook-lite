import { App } from 'src/app/apps/interfaces/app.interface';

export interface Message {
  id: number;
  app: App;
  message: string;
  link: string;
  createdAt: Date;
}

export interface Ids {
  ids: any;
}
