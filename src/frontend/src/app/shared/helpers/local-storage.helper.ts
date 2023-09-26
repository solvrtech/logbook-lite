import { environment } from 'src/environments/environment';

/**1
 * Save the given value into localStorage with the given key (prefixed)
 * @param key key for the stored value
 * @param value value to be stored
 */
export function saveToLocalStorage(key: string, value: string) {
  localStorage.setItem(`${environment.localStoragePrefix + key}`, value);
}

/**
 * Get the prefixed stored variable from localStorage
 * @param key key of the stored variable
 */
export function getFromLocalStorage(key: string): string | any {
  return localStorage.getItem(`${environment.localStoragePrefix + key}`);
}

/**
 * Remove the variable with the given key (prefixed) from localStorage
 * @param key key of the stored variable
 */
export function removeFromLocalStorage(key: string) {
  localStorage.removeItem(`${environment.localStoragePrefix + key}`);
}
