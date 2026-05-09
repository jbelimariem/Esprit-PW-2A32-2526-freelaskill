import os
import re

for root, dirs, files in os.walk('Views'):
    for file in files:
        if file.endswith('.php'):
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
                
            # Replace Category_prod model with controller
            content = content.replace("require_once __DIR__ . '/../../Models/Category_prod.php';", "require_once __DIR__ . '/../../controllers/Category_prodController.php';")
            content = content.replace("require_once __DIR__ . '/../Models/Category_prod.php';", "require_once __DIR__ . '/../controllers/Category_prodController.php';")
            
            content = content.replace("$categoryModel  = new Category_prod();", "$categoryController = new Category_prodController();")
            content = content.replace("$categoryModel = new Category_prod();", "$categoryController = new Category_prodController();")
            content = content.replace("$categoryModel->", "$categoryController->")
            
            # Replace Produit model with controller
            content = content.replace("require_once __DIR__ . '/../../Models/Produit.php';", "require_once __DIR__ . '/../../controllers/produitController.php';")
            content = content.replace("require_once __DIR__ . '/../Models/Produit.php';", "require_once __DIR__ . '/../controllers/produitController.php';")
            
            content = content.replace("$produitModel  = new Produit();", "$produitController = new ProduitController();")
            content = content.replace("$productModel   = new Produit();", "$produitController = new ProduitController();")
            content = content.replace("$produitModel = new Produit();", "$produitController = new ProduitController();")
            content = content.replace("$productModel = new Produit();", "$produitController = new ProduitController();")
            
            content = content.replace("$produitModel->", "$produitController->")
            content = content.replace("$productModel->", "$produitController->")
            
            # Replace Commande model with controller
            content = content.replace("require_once __DIR__ . '/../../Models/commande.php';", "require_once __DIR__ . '/../../controllers/commandeController.php';")
            content = content.replace("require_once __DIR__ . '/../Models/commande.php';", "require_once __DIR__ . '/../controllers/commandeController.php';")
            
            content = content.replace("$commandeModel = new Commande();", "$commandeController = new CommandeController();")
            content = content.replace("$commandeModel->", "$commandeController->")
            
            # Replace method names
            content = content.replace("->getAll()", "->getAllData()")
            content = content.replace("->getById(", "->getByIdData(")
            content = content.replace("->create(", "->createData(")
            content = content.replace("->update(", "->updateData(")
            content = content.replace("->delete(", "->deleteData(")
            content = content.replace("->updateStatut(", "->updateStatutData(")
            content = content.replace("->getByStatut(", "->getByStatutData(")
            content = content.replace("->getByUser(", "->getByUser(") # already same in commandeController
            
            # Special for category because I used getAll() instead of getAllData() inside Category_prodController. Let's fix that.
            # Actually, I should just make Category_prodController use getAllData too to align it.
            
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)

print("MVC usages in Views updated.")
