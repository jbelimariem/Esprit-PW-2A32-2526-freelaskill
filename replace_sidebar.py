import os
import re

dir_path = r'Views\Backoffice'

sidebar_old_regex1 = r'<a href="\./categories\.php" class="admin-nav-item(?: active)?">\s*<i class="fa-solid fa-tags"></i> Catégories\s*</a>'

sidebar_new_template = """                <a href="./ajouter_categorie.php" class="admin-nav-item{active_ajouter}">
                    <i class="fa-solid fa-plus"></i> Ajouter Catégorie
                </a>
                <a href="./liste_categories.php" class="admin-nav-item{active_liste}">
                    <i class="fa-solid fa-list"></i> Liste des Catégories
                </a>
                <a href="./mes_achats.php" class="admin-nav-item{active_achats}">
                    <i class="fa-solid fa-bag-shopping"></i> Mes Achats
                </a>"""

for fname in os.listdir(dir_path):
    if fname.endswith('.php'):
        fpath = os.path.join(dir_path, fname)
        with open(fpath, 'r', encoding='utf-8') as f:
            content = f.read()

        active_aj = ' active' if fname == 'ajouter_categorie.php' else ''
        active_li = ' active' if fname == 'liste_categories.php' else ''
        active_ac = ' active' if fname == 'mes_achats.php' else ''

        new_sidebar = sidebar_new_template.format(active_ajouter=active_aj, active_liste=active_li, active_achats=active_ac)
        
        # also replacing active class if the file was categories.php before we split
        content = re.sub(sidebar_old_regex1, new_sidebar, content)
        
        # Fix the requires based on our new MVC
        if fname in ['ajouter_categorie.php', 'liste_categories.php']:
            content = content.replace("require_once __DIR__ . '/../../Models/Category_prod.php';", "require_once __DIR__ . '/../../controllers/Category_prodController.php';")
            content = content.replace("$categoryModel  = new Category_prod();", "$categoryController = new Category_prodController();")
            content = content.replace("$categoryModel = new Category_prod();", "$categoryController = new Category_prodController();")
            content = content.replace("$categoryModel->", "$categoryController->")
            content = content.replace("create(", "createData(")
            content = content.replace("update(", "updateData(")
            content = content.replace("delete(", "deleteData(")

        # Same requires fix for dashboard.php
        if fname == 'dashboard.php':
            content = content.replace("require_once __DIR__ . '/../../Models/Category_prod.php';", "require_once __DIR__ . '/../../controllers/Category_prodController.php';")
            content = content.replace("require_once __DIR__ . '/../../Models/Produit.php';", "require_once __DIR__ . '/../../controllers/produitController.php';\nif (session_status() === PHP_SESSION_NONE) {\n    session_start();\n}\nif (!isset($_SESSION['admin_id'])) {\n    $_SESSION['admin_id'] = 1;\n}")
            content = content.replace("$categoryModel  = new Category_prod();", "$categoryController = new Category_prodController();")
            content = content.replace("$productModel   = new Produit();", "$productController = new ProduitController();")
            content = content.replace("$categoryModel->", "$categoryController->")
            content = content.replace("$productModel->", "$productController->")
            content = content.replace("create(", "createData(")
            content = content.replace("update(", "updateData(")
            content = content.replace("delete(", "deleteData(")
            content = content.replace("updateStatut(", "updateStatutData(")
            content = content.replace("getByStatut(", "getByStatutData(")

        # Save back
        with open(fpath, 'w', encoding='utf-8') as f:
            f.write(content)

print("Sidebar and requires updated.")
