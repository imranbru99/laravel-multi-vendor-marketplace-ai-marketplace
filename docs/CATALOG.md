# Catalog publishing and imports

Master products contain names, categories/brands, descriptions, attributes, galleries and product type. Seller offers contain SKU, price, optional master variant and warehouse inventory. Variants belong to a master product and capture descriptive attributes/barcode/weight. Checkout snapshots the chosen variant, prices and policy results.

Sellers submit Product Proposals with their own warehouse, category, original description, SKU, minor-unit price, initial quantity and distribution-rights confirmation. Optional JPEG/PNG/WebP images remain private before review. Submit moves draft/rejected → pending. Authorized reviewers approve publication or reject with notes. Approval is idempotent, checks plan quota/SKU/vendor status, creates the published product and offer, and records initial stock through the immutable movement service. Approved images are copied to public tenant catalog storage; uploads are limited to 2 MB. `storage:link` is required locally.

CSV imports remain private, limited to 2 MB and 1000 rows. See examples/catalog-import.csv. Required headers: name,description,category_slug,sku,price_minor,quantity. Validation reports row errors, missing categories, duplicate/existing SKU and malformed amounts. A clean scan can stage once into pending proposals; it never bypasses human approval. Rights confirmation is required. This importer is for new physical products, not destructive price/stock updates or variant import.

Renamed product, seller and content URLs reserve their previous tenant/entity slugs and redirect directly to the latest public URL. Draft/unapproved targets are not exposed. An unrelated record cannot claim a reserved historical URL. Trusted direct SQL edits bypass model events and are outside this workflow.
