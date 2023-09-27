export interface User {
  id: number;
  name: string;
  email?: string;
  role?: string;
  mfa?: string;
  assigned?: Assigned[];
}

export interface Assigned {
  app: number;
  team: number;
}

export interface RequestUser {
  name: string;
  email: string;
  role: string;
  password: string;
}

export interface TwoFactor {
  mfaStatus: boolean;
  mfaMethod: string;
  userEmail: string;
}
