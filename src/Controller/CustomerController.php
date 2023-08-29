<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CustomerFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/customer')]
class CustomerController extends AbstractController
{

    public EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/list', name: 'app_customer', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $repository = $this->em->getRepository(Customer::class);
        $filters = $request->query->all();
        $customers = $repository->findAllByFilters($filters);

        return $this->render('customer/index.html.twig', [
            'customers' => $customers,
        ]);
    }

    #[Route('/ajax_form_customer', name: 'ajax_customer')]
    public function ajax_form_customer(Request $request): Response
    {

    }

    #[Route('/edit/{id}', name: 'edit_customer')]
    public function edit(Request $request)
    {
        $id = $request->get('id');
        $repository = $this->em->getRepository(Customer::class);
        $customer = ($repository->find($id));
        if (!$customer) {
            return $this->redirectToRoute('app_customer');
        }
        $form = $this->createForm(CustomerFormType::class, $customer);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setName($form->get('name')->getData());
            $customer->setFirstname($form->get('firstname')->getData());
            $customer->setPhone($form->get('phone')->getData());
            $customer->setAddress($form->get('address')->getData());
            $customer->setCity($form->get('city')->getData());
            $customer->setCountry($form->get('country')->getData());

            $this->em->persist($customer);
            $this->em->flush();
            return $this->redirectToRoute('app_customer');
        }
        return $this->render('customer/form.html.twig', [
            'customerForm' => $form->createView(),
            'page_title' => 'Edit a customer'
        ]);
    }
    

    #[Route('/add', name: 'add_customer')]
    public function add(Request $request): Response
    {
        $customer = new Customer();
        $form = $this->createForm(CustomerFormType::class, $customer);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setName($form->get('name')->getData());
            $customer->setFirstname($form->get('firstname')->getData());
            $customer->setPhone($form->get('phone')->getData());
            $customer->setAddress($form->get('address')->getData());
            $customer->setCity($form->get('city')->getData());
            $customer->setCountry($form->get('country')->getData());

            $this->em->persist($customer);
            $this->em->flush();
            return $this->redirectToRoute('app_customer');
        }
        return $this->render('customer/form.html.twig', [
            'customerForm' => $form->createView(),
            'page_title' => 'Add a customer'
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/delete/{id}', name: 'delete_customer' , methods: ['GET'])]
    public function delete(Request $request)
    {
        $id = $request->get('id');
        $repository = $this->em->getRepository(Customer::class);
        $customer = ($repository->find($id));
        if (!$customer) {
            return $this->redirectToRoute('app_customer');
        }
        $this->em->remove($customer);
        $this->em->flush();
        return $this->redirectToRoute('app_customer');
    }    
}
