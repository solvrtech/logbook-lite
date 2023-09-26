export interface DataRole {
  info: string;
  roles: Role[];
}

export interface Role {
  role: string;
  permissions: string[];
}
