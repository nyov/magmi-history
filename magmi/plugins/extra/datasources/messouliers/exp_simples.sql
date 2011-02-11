SELECT  'simple' as type,
'admin' as store,
'base' as websites,
CONCAT(lcv.CodeArticleLCV,'-',lcv.Taille) as sku,
'Chaussure' as attribute_set,
0 as 'has_options',
CONCAT(mev.marque,' ',mev.model_label,' (',mev.Couleur,')') as name,
CONCAT(mev.marque,' ',mev.model_label,' (',mev.Couleur,')') as title,
mev.description as meta_description,
mev.Price as price,
'TVA' as tax_class_id,
1 as status,
4 as visibility,
mev.description as description,
mev.description as short_description,
lcv.QteStock as qty,
lcv.Taille as pointure
FROM ms_import_lcv as lcv
JOIN ms_import_mevia as mev ON mev.CodeArticleLCV=lcv.CodeArticleLCV