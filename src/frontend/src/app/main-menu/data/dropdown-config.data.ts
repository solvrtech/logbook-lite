import { TEAM_MANAGER, TEAM_STANDARD } from 'src/app/administration/data/role.data';
import { FormDropdown } from 'src/app/shared/interfaces/common.interface';
import { SearchFieldOptionValue } from 'src/app/shared/interfaces/search.interface';
import { CRITICAL_PRIORITY, HIGHEST_PRIORITY, HIGH_PRIORITY, LOW_PRIORITY, MEDIUM_PRIORITY } from './priority.data';
import { ALERT, CRITICAL, DEBUG, EMERGENCY, ERROR, INFO, NOTICE, WARNING } from './severity.data';
import { CACHE, CPU_LOAD, DATABASE, MEMORY, USED_DISK } from './specific.data';
import { IGNORED, NEW, ON_REVIEW, RESOLVED } from './status.data';

// Dropdown severity
export const DROPDOWN_SEVERITIES: FormDropdown[] = [
  { value: EMERGENCY, description: 'severity.EMERGENCY' },
  { value: ALERT, description: 'severity.ALERT' },
  { value: CRITICAL, description: 'severity.CRITICAL' },
  { value: ERROR, description: 'severity.ERROR' },
  { value: NOTICE, description: 'severity.NOTICE' },
  { value: INFO, description: 'severity.INFO' },
  { value: DEBUG, description: 'severity.DEBUG' },
  { value: WARNING, description: 'severity.WARNING' },
];

// Dropdown notify
export const DROPDOWN_NOTIFIES: FormDropdown[] = [
  { value: 'ALL', description: 'common.all' },
  { value: TEAM_MANAGER, description: 'team.' + TEAM_MANAGER },
  { value: TEAM_STANDARD, description: 'team.' + TEAM_STANDARD },
];

// Dropdown checkey
export const DROPDOWN_CHECKKEYS: FormDropdown[] = [
  { value: CACHE, description: 'health.cache' },
  { value: CPU_LOAD, description: 'health.cpu-load' },
  { value: DATABASE, description: 'health.database' },
  { value: MEMORY, description: 'health.memory' },
  { value: USED_DISK, description: 'health.used-disk' },
];

// Dropdown source
export const DROPDOWN_SOURCES: FormDropdown[] = [
  { value: 'log', description: 'common.logs' },
  { value: 'health', description: 'common.health_signal' },
];

// Dropdown priorities
export const DROPDOWN_PRIORITIES: FormDropdown[] = [
  { value: HIGHEST_PRIORITY, description: 'priority.highest' },
  { value: CRITICAL_PRIORITY, description: 'priority.critical' },
  { value: HIGH_PRIORITY, description: 'priority.high' },
  { value: MEDIUM_PRIORITY, description: 'priority.medium' },
  { value: LOW_PRIORITY, description: 'priority.low' },
];

export const DROPDOWN_STATUS_CONFIG: FormDropdown[] = [
  { value: NEW, description: 'common.new' },
  { value: ON_REVIEW, description: 'common.on_review' },
  { value: IGNORED, description: 'common.ignored' },
  { value: RESOLVED, description: 'common.resolved' },
];

// Search dropdown severity
export const SEARCH_DROPDOWN_SEVERITY: SearchFieldOptionValue[] = [
  { value: EMERGENCY, label: 'severity.EMERGENCY' },
  { value: ALERT, label: 'severity.ALERT' },
  { value: CRITICAL, label: 'severity.CRITICAL' },
  { value: ERROR, label: 'severity.ERROR' },
  { value: WARNING, label: 'severity.WARNING' },
  { value: NOTICE, label: 'severity.NOTICE' },
  { value: INFO, label: 'severity.INFO' },
  { value: DEBUG, label: 'severity.DEBUG' },
];

export const SEARCH_DROPDOWN_STATUS_CONFIG: SearchFieldOptionValue[] = [
  { value: NEW, label: 'common.new' },
  { value: ON_REVIEW, label: 'common.on_review' },
  { value: IGNORED, label: 'common.ignored' },
  { value: RESOLVED, label: 'common.resolved' },
];
