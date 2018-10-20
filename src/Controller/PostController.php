<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Form\PostType;
use App\Form\CommentType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class PostController extends AbstractController
{
    /**
     * @Route("/", name="post_index", methods="GET")
     */
    public function index(Request $request, PostRepository $postRepository): Response
    {
        $user_filter = $request->query->get('username');
        if ($user_filter === null)
        {
            $posts = $postRepository->findAll();

        } else {

            $posts = $postRepository->findByAuthor($user_filter);
        }
        return $this->render('post/index.html.twig', ['posts' => $posts]);
    }

    /**
     * @Route("/new", name="post_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('user_login');
        }
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $user = $this->getUser();

            $post->setCreatedAt(new \DateTime)
                ->setAuthor($user);

            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="post_show", methods="GET|POST", requirements={"id": "\d+"})
     */
    public function show(Post $post, Request $request): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $comment->setAuthor($user)->setPost($post);
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
        }
        return $this->render('post/show.html.twig', ['post' => $post, 'form' => $form->createView()]);
    }
}
