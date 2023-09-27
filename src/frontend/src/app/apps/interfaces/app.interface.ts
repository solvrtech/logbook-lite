export interface App extends AppStandard {
  apiKey: string;
  appHealth: Date;
  description: string;
  teamApp: any[];
  appHealthSetting: AppHealthSetting;
}

export interface AppStandard {
  id: string;
  name: string;
  type: string;
  appLogo: string;
  isTeamManager: boolean;
  appHealthSetting: AppHealthSetting;
}

export interface AppHealthSetting {
  isEnabled: boolean;
  url: string;
  period: string;
}

export interface AppVersion {
  app: string;
  core: string;
}

export interface Specific {
  checkKey: string;
  item: string;
  value: string;
  gate: string;
}

export interface Severity {
  level: [];
}

export interface GeneralHealth {
  id: string;
  createdAt: Date;
  status: string;
}

export interface AppLogo {
  type?: 'image' | 'icon';
  value?: string;
}

export interface RequestApp {
  name: string;
  description: string;
  team?: any;
  type: string;
  logo: string;
  updateLogo?: boolean;
}

export interface MailSetting {
  smtpHost: string;
  smtpPort: number;
  username: string;
  password: string;
  encryption: string;
  fromEmail: string;
  fromName: string;
  testEmail?: string;
  token?: string;
}
