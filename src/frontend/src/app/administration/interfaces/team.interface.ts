import { App } from 'src/app/apps/interfaces/app.interface';

export interface Team {
  id: number;
  name: string;
  isTeamManager: boolean;
  totalApp: number;
  userTeam: UserTeam[];
  apps: App[];
}

export interface UserTeam {
  userId: number;
  role: string;
}
