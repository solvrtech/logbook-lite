export interface Health {
  id: number;
  status: string;
  createdAt: string;
  app: string;
  appType: string;
  appLogo: string;
  totalFailed: number;
  healthCheck: HealthCheck[];
}

export interface HealthCheck {
  checkKey: string;
  status: string;
  meta?: Meta | any;
}

export interface Meta {
  cpuLoad?: any;
  usedDiskSpace?: string;
  unit?: string;
  redisSize?: string | any;
  memoryUsage?: string;
  databaseSize?: any;
}
