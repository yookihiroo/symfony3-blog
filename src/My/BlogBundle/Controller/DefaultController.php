<?php

namespace My\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use My\BlogBundle\Entity\Post;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('MyBlogBundle:Post')->findAll();
        return $this->render('MyBlogBundle:Default:index.html.twig', ['posts' => $posts]);
    }

    public function newAction(Request $request)
    {
        $form = $this->createFormBuilder(new Post())
            ->add('title')
            ->add('body')
            ->getForm();

        if ('POST' == $request->getMethod()) {
            $params = $request->request->all()['form'];
            $form->submit($params);
            if ($form->isValid()) {
                // エンティティを永続化
                $post = $form->getData();
                $post->setCreatedAt(new \DateTime());
                $post->setUpdatedAt(new \DateTime());

                $em = $this->getDoctrine()->getManager();
                $em->persist($post);
                $em->flush();
                return $this->redirect($this->generateUrl('blog_index'));
            }
        }

        // 描画
        return $this->render('MyBlogBundle:Default:new.html.twig', array(
                'form' => $form->createView(),
        ));
    }

    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->find('MyBlogBundle:Post', $id);
        return $this->render('MyBlogBundle:Default:show.html.twig', ['post' => $post]);
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->find('MyBlogBundle:Post', $id);
        if (!$post) {
            throw new NotFoundHttpException('The post does not exist.');
        }
        $em->remove($post);
        $em->flush();
        return $this->redirect($this->generateUrl('blog_index'));
    }

    public function editAction(Request $request, $id)
    {
        // DBから取得
        $em = $this->getDoctrine()->getManager();
        $post = $em->find('MyBlogBundle:Post', $id);
        if (!$post) {
            throw new NotFoundHttpException('The post does not exist.');
        }

        // フォームのビルド
        $form = $this->createFormBuilder($post)
            ->add('title')
            ->add('body')
            ->getForm();

        // バリデーション
        if ('POST' === $request->getMethod()) {
            $params = $request->request->all()['form'];
            $form->submit($params);
            if ($form->isValid()) {
                // 更新されたエンティティをデータベースに保存
                $post = $form->getData();
                $post->setUpdatedAt(new \DateTime());
                $em->flush();
                return $this->redirect($this->generateUrl('blog_index'));
            }
        }

        // 描画
        return $this->render('MyBlogBundle:Default:edit.html.twig', array(
            'post' => $post,
            'form' => $form->createView(),
        ));
    }
}
