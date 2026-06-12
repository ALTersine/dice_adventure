<?php

namespace App\Controller;

use App\Factory\PrixFactory;
use App\Repository\AvisRepository;
use App\Repository\FraisKMRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Tableau de bord d'administration réservé aux utilisateurs connectés :
 * gestion des prix (et de leurs infos), des frais kilométriques et des avis.
 */
#[IsGranted('ROLE_ADMIN')]
#[Route('/ecranMJ')]
class AdminController extends AbstractController
{
    #[Route('', name: 'app_admin', methods: ['GET'])]
    public function index(
        PrixFactory $prixFactory,
        FraisKMRepository $fraisKMRepository,
        AvisRepository $avisRepository,
    ): Response {
        return $this->render('admin/index.html.twig', [
            'prixList'          => $prixFactory->readAll(),
            'fraisKilometrique' => $fraisKMRepository->getInfo(),
            'avisList'          => $avisRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    // ----------------------------------------------------------------
    //  PRIX (+ infos)
    // ----------------------------------------------------------------

    #[Route('/prix/new', name: 'app_admin_prix_new', methods: ['POST'])]
    public function prixNew(Request $request, PrixFactory $prixFactory): Response
    {
        if (!$this->isCsrfTokenValid('admin_prix_new', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide, veuillez réessayer.');

            return $this->redirectToRoute('app_admin');
        }

        $prixFactory->create(
            nom: trim((string) $request->request->get('nom')),
            prixAffiche: trim((string) $request->request->get('prixAffiche')),
            prixPar: trim((string) $request->request->get('prixPar')),
            mettreEnAvant: $request->request->getBoolean('mettreEnAvant'),
            formuleContact: $this->nullableString($request->request->get('formuleContact')),
            infos: $this->parseInfos($request->request->get('infos')),
        );

        $this->addFlash('success', 'Formule créée avec succès.');

        return $this->redirectToRoute('app_admin');
    }

    #[Route('/prix/{id}/edit', name: 'app_admin_prix_edit', methods: ['POST'])]
    public function prixEdit(int $id, Request $request, PrixFactory $prixFactory): Response
    {
        $prix = $prixFactory->read($id);
        if (null === $prix) {
            throw $this->createNotFoundException('Formule introuvable.');
        }

        if (!$this->isCsrfTokenValid('admin_prix_edit_'.$id, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide, veuillez réessayer.');

            return $this->redirectToRoute('app_admin');
        }

        $prixFactory->update(
            $prix,
            nom: trim((string) $request->request->get('nom')),
            prixAffiche: trim((string) $request->request->get('prixAffiche')),
            prixPar: trim((string) $request->request->get('prixPar')),
            mettreEnAvant: $request->request->getBoolean('mettreEnAvant'),
            formuleContact: $this->nullableString($request->request->get('formuleContact')),
            infos: $this->parseInfos($request->request->get('infos')),
        );

        $this->addFlash('success', 'Formule mise à jour.');

        return $this->redirectToRoute('app_admin');
    }

    #[Route('/prix/{id}/delete', name: 'app_admin_prix_delete', methods: ['POST'])]
    public function prixDelete(int $id, Request $request, PrixFactory $prixFactory): Response
    {
        $prix = $prixFactory->read($id);
        if (null === $prix) {
            throw $this->createNotFoundException('Formule introuvable.');
        }

        if (!$this->isCsrfTokenValid('admin_prix_delete_'.$id, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide, veuillez réessayer.');

            return $this->redirectToRoute('app_admin');
        }

        $prixFactory->delete($prix);
        $this->addFlash('success', 'Formule supprimée.');

        return $this->redirectToRoute('app_admin');
    }

    // ----------------------------------------------------------------
    //  FRAIS KILOMÉTRIQUES
    // ----------------------------------------------------------------

    #[Route('/frais-km', name: 'app_admin_fraiskm', methods: ['POST'])]
    public function fraisKm(Request $request, FraisKMRepository $fraisKMRepository): Response
    {
        if (!$this->isCsrfTokenValid('admin_fraiskm', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide, veuillez réessayer.');

            return $this->redirectToRoute('app_admin');
        }

        $fraisKMRepository->replaceInfo(trim((string) $request->request->get('info')));
        $this->addFlash('success', 'Frais kilométriques mis à jour.');

        return $this->redirectToRoute('app_admin');
    }

    // ----------------------------------------------------------------
    //  AVIS
    // ----------------------------------------------------------------

    #[Route('/avis/new', name: 'app_admin_avis_new', methods: ['POST'])]
    public function avisNew(Request $request, AvisRepository $avisRepository): Response
    {
        if (!$this->isCsrfTokenValid('admin_avis_new', (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide, veuillez réessayer.');

            return $this->redirectToRoute('app_admin');
        }

        $note = (int) $request->request->get('note');
        $note = max(0, min(5, $note));

        $avisRepository->create(
            auteur: trim((string) $request->request->get('auteur')),
            message: trim((string) $request->request->get('message')),
            note: $note,
            infosSuppAuteur: $this->nullableString($request->request->get('infosSuppAuteur')),
        );

        $this->addFlash('success', 'Avis ajouté.');

        return $this->redirectToRoute('app_admin');
    }

    #[Route('/avis/{id}/delete', name: 'app_admin_avis_delete', methods: ['POST'])]
    public function avisDelete(int $id, Request $request, AvisRepository $avisRepository): Response
    {
        $avis = $avisRepository->find($id);
        if (null === $avis) {
            throw $this->createNotFoundException('Avis introuvable.');
        }

        if (!$this->isCsrfTokenValid('admin_avis_delete_'.$id, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide, veuillez réessayer.');

            return $this->redirectToRoute('app_admin');
        }

        $avisRepository->delete($avis);
        $this->addFlash('success', 'Avis supprimé.');

        return $this->redirectToRoute('app_admin');
    }

    // ----------------------------------------------------------------
    //  Helpers
    // ----------------------------------------------------------------

    /**
     * Transforme le contenu d'un textarea (une info par ligne) en tableau nettoyé.
     *
     * @return string[]
     */
    private function parseInfos(?string $raw): array
    {
        if (null === $raw || '' === trim($raw)) {
            return [];
        }

        $lignes = preg_split('/\r\n|\r|\n/', $raw) ?: [];

        return array_values(array_filter(
            array_map('trim', $lignes),
            static fn (string $ligne): bool => '' !== $ligne,
        ));
    }

    /**
     * Renvoie null si la chaîne est vide (après trim), sinon la chaîne nettoyée.
     */
    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return '' === $value ? null : $value;
    }
}
