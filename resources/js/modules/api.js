/**
 * Centralized API Client Module
 * Built adhering to ES2023+ standards with strict error handling, 
 * automatic CSRF token management, and async/await HTTP operations.
 *
 * @module api
 */

import axios from 'axios';

/**
 * Gets the CSRF token from the HTML meta tag.
 *
 * @returns {string|null} The CSRF token or null if not found.
 */
export function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta?.getAttribute('content') ?? null;
}

/**
 * Configure default headers for global Axios instance.
 */
if (window.axios) {
  window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
  const token = getCsrfToken();
  if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
  }
}

/**
 * Sends a POST request to an API endpoint with error handling and CSRF protection.
 *
 * @template T
 * @param {string} url - Target API URL
 * @param {Record<string, unknown>} [payload={}] - Request body payload
 * @param {Record<string, string>} [headers={}] - Custom headers
 * @returns {Promise<{ success: boolean; data: T | null; error: string | null; status: number }>}
 */
export async function postApi(url, payload = {}, headers = {}) {
  try {
    const token = getCsrfToken();
    const requestHeaders = {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(token ? { 'X-CSRF-TOKEN': token } : {}),
      ...headers,
    };

    const response = await fetch(url, {
      method: 'POST',
      headers: requestHeaders,
      body: JSON.stringify(payload),
    });

    const isJson = response.headers.get('content-type')?.includes('application/json');
    const data = isJson ? await response.json() : null;

    if (!response.ok) {
      const errorMessage = data?.message ?? `Request failed with status ${response.status}`;
      return { success: false, data: null, error: errorMessage, status: response.status };
    }

    return { success: true, data, error: null, status: response.status };
  } catch (err) {
    const errorMsg = err instanceof Error ? err.message : 'An unknown network error occurred';
    console.error(`[API Error] POST ${url}:`, err);
    return { success: false, data: null, error: errorMsg, status: 0 };
  }
}

/**
 * Sends a GET request to an API endpoint with error handling.
 *
 * @template T
 * @param {string} url - Target API URL
 * @param {Record<string, string>} [headers={}] - Custom headers
 * @returns {Promise<{ success: boolean; data: T | null; error: string | null; status: number }>}
 */
export async function getApi(url, headers = {}) {
  try {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...headers,
      },
    });

    const isJson = response.headers.get('content-type')?.includes('application/json');
    const data = isJson ? await response.json() : null;

    if (!response.ok) {
      const errorMessage = data?.message ?? `Request failed with status ${response.status}`;
      return { success: false, data: null, error: errorMessage, status: response.status };
    }

    return { success: true, data, error: null, status: response.status };
  } catch (err) {
    const errorMsg = err instanceof Error ? err.message : 'An unknown network error occurred';
    console.error(`[API Error] GET ${url}:`, err);
    return { success: false, data: null, error: errorMsg, status: 0 };
  }
}
