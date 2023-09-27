export interface AuthResponse {
  data?: any;
  success?: boolean;
  message?: string;
  accessTokenExpiration: number;
  refreshTokenExpiration: number;
}
