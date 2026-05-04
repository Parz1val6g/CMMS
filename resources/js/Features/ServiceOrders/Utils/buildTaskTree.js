/**
 * buildTaskTree — Transforms a flat array into a nested tree.
 *
 * Generic utility that supports any parent-child key naming.
 * Each item must have an `id`. Children are matched via `parentKey` (default `parent_id`).
 * Roots are items where the parentKey value is null, undefined, or not present.
 *
 * @param {Array<Object>} items  - Flat list of items
 * @param {Object}        opts
 * @param {string}        [opts.parentKey='parent_id'] - FK field pointing to parent's id
 * @param {string}        [opts.idKey='id']            - PK field
 * @returns {Array<{item: Object, children: Array}>}
 */
export default function buildTaskTree(items, opts = {}) {
  const { parentKey = 'parent_id', idKey = 'id' } = opts;

  if (!Array.isArray(items) || items.length === 0) return [];

  const map = new Map();
  const roots = [];

  // First pass — create nodes
  for (const item of items) {
    map.set(item[idKey], { item, children: [] });
  }

  // Second pass — link children to parents
  for (const item of items) {
    const parentId = item[parentKey];
    const node = map.get(item[idKey]);
    if (!node) continue;

    if (parentId != null && map.has(parentId)) {
      map.get(parentId).children.push(node);
    } else {
      roots.push(node);
    }
  }

  return roots;
}
