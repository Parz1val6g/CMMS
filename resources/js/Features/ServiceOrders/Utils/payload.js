/**
 * @param {FormData} formData
 * @param {string|null} clientLocationId
 * @param {boolean} locationsDirty
 * @param {string[]} locationFields
 */
export function buildCreatePayload(formData, clientLocationId, locationsDirty, locationFields) {
  if (clientLocationId && !locationsDirty) {
    // Scenario A: Location selected + no edits → send client_location_id, omit 6 fields
    formData.append('client_location_id', clientLocationId);
    locationFields.forEach(f => formData.delete(f));
    return;
  }

  if (clientLocationId && locationsDirty) {
    // Scenario B: Location selected + user edited → keep fields as-is, no client_location_id
    // (selector already cleared, dirty signalled by ClientLocationSelector)
    return;
  }

  // Scenario C: No location selected → default behavior, all fields sent
}
