import { Language } from 'src/app/shared/interfaces/common.interface';

export interface SettingSecurity {
  mfaAuthentication: boolean;
  mfaDelayResend: number;
  mfaMaxResend: number;
  mfaMaxFailed: number;
  loginInterval: number;
  loginMaxFailed: number;
}

export interface SettingGlobal {
  securitySetting: SettingSecurity;
  generalSetting: Language;
}
