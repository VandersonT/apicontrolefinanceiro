<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect;

/*--------Models--------*/
use App\Models\User;
/*----------------------*/

class UserController extends Controller{

    public function userAuthenticate(Request $request){
        $array = ['error' => ''];
        $array['isUserLoggedIn'] = false;

        if(empty($request->token)){
            $array['error'] = 'Envie o campo (token) preenchido.';
            return $array;
        }
        
        $userFound = User::where('token', $request->token)->first();

        if(!$userFound){
            $array['error'] = 'Este usuário não esta logado';
            return $array;
        }

        $getFilteredInformation = [
            'id' => $userFound['id'],
            'name' => $userFound['name'],
            'email' => $userFound['email'],
            'access' => $userFound['access'],
            'avatar' => $userFound['avatar'],
            'theme' => $userFound['theme']
        ];

        $array['loggedUser'] = $getFilteredInformation;
        $array['isUserLoggedIn'] = true;

        return $array;
    }

    public function regiterNewUser(Request $request){
        $array = ['error' => ''];

        $name = filter_var($request->name, FILTER_SANITIZE_STRING);
        $email = filter_var($request->email, FILTER_VALIDATE_EMAIL);
        $pass = filter_var($request->pass, FILTER_SANITIZE_STRING);
        $confirmPass = filter_var($request->confirmPass, FILTER_SANITIZE_STRING);
        /**/
        if(!($name || !$pass || !$confirmPass)){
            $array['error'] = 'Não envie campos vazios';
            return $array;
        }

        if(strlen($name) > 15){
            $array['error'] = 'O nome só pode conter até 15 caracteres.';
            return $array;
        }
        
        if(!$email){
            $array['error'] = 'O Email enviado não é valido';
            return $array;
        }

        $emailInUse = User::where('email', $email)->first();

        if($emailInUse){
            $array['error'] = 'Este email já está registrado, faça o login.';
            return $array;
        }

        if($pass != $confirmPass){
            $array['error'] = 'As senhas não coincidem, favor verificar novamente.';
            return $array;
        }

        $hash = password_hash($request->pass, PASSWORD_DEFAULT);
        $token = md5(time().rand(0,9000).rand(0,500));

        $registerUser = new User;
            $registerUser->name = $name;
            $registerUser->email = $email;
            $registerUser->access = 0;
            $registerUser->password = $hash;
            $registerUser->token = $token;
            $registerUser->avatar = url('/').'/media/no-picture.png';
        $registerUser->save();

        //Send email to confirm account
        $to = $email;

        $subject = 'Confirmação de Conta';

        $body = 'Opa, tudo bom? Estamos a um passo de confirmar a sua conta no nosso sistema "Controle Financeiro."'."\r\n".
                'Para concluir a ação e ter acesso 100% a todas as funcionalidades basta clicar no link abaixo.'."\r\n".
                'Link: '.url('/').'/api/confirmAccount/'.$registerUser->id."\r\n".
                'Se não reconhece essa ação, basta ignorar este email.';

        $header = "From: suporte@apicontrolefinanceiro.ga"."\r\n".
                "Reply-To: suporte@apicontrolefinanceiro.ga"."\r\n".
                "X-Mailer: PHP/".phpversion();
    
        mail($to, $subject, $body, $header);
        /***/

        $array['success'] = 'Usuário registrado com successo;';
        $array['token'] = $token;
        return $array;
    }

    public function confirmAccount(Request $request){
        echo 'teste';
        return 'oi';
        $array = ['error' => ''];

        $accountToConfirm = User::find($request->id);

            //Check if the user was found
            if(!$accountToConfirm){
                $array['error'] = 'Ocorreu um erro inesperado durante a confirmação.';
                return $array;
            }

            $accountToConfirm->access = 1;

        $accountToConfirm->save();

        return Redirect::to('https://controlefinanceiro-delta.vercel.app/contaAtiva');
    }

    public function loginAction(Request $request){
        $array = ['error' => ''];

        $email = filter_var($request->email, FILTER_VALIDATE_EMAIL);
        $pass = filter_var($request->pass, FILTER_SANITIZE_STRING);

        if(!$email || !$pass){
            $array['error'] = 'Preencha todos os campos corretamente.';
            return $array;
        }

        $user = User::where('email', $email)->first();

        if($user){
            if(password_verify($pass, $user['password'])){
                $array['token'] = $user->token;
                return $array;
            }
        }
        $array['error'] = 'Email e/ou senha esta(ão) incorreto(s).';
        return $array;
    }

    public function editUser(Request $request){
        $array = ['error' => ''];

        $userId =  $request->id;
        $name = filter_var($request->name, FILTER_SANITIZE_STRING);
        $email = $request->email;
        $theme = filter_var($request->theme, FILTER_SANITIZE_STRING);
        $oldPass = filter_var($request->oldPass, FILTER_SANITIZE_STRING);
        $newPass = filter_var($request->newPass, FILTER_SANITIZE_STRING);
        $avatarUpdated = filter_var($request->avatarUpdated, FILTER_SANITIZE_STRING);
        $changedSomething = false;
        

        if(!$userId){
            $array['error'] = 'Envie o id da conta que quer editar.';
            return $array; 
        }

        if(strlen($name) > 15){
            $array['error'] = 'O nome só pode conter até 15 caracteres.';
            return $array;
        }
        
        $user = User::find($userId);

            if(!$user){
                $array['error'] = 'Usuário não encontrado.';
                return $array; 
            }

            if($name){
                $user->name = $name;
                $changedSomething = true;
            }

            if($request->email){
                $email = filter_var($request->email, FILTER_VALIDATE_EMAIL);

                if(!$email){
                    $array['error'] = 'O email enviado esta invalido.';
                    return $array; 
                }

                if($email != $user->email){
                    $user->email = $email;
                    $user->access = 0;
                    $changedSomething = true;
                }
                
            }

            if($theme){
                $user->theme = $theme;
                $changedSomething = true;
            }

            if($newPass){
                if(!$oldPass){
                    $array['error'] = 'Para trocar de senha precisa enviar a antiga também.';
                    return $array;
                }

                //verifica se a senha está correta
                if(!password_verify($oldPass, $user['password'])){
                    $array['error'] = 'A senha enviada está errada!';
                    return $array;
                }

                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $user->password = $hash;
                $changedSomething = true;
            }

            if(!$changedSomething){
                $array['error'] = 'Modifique alguma coisa antes de atualizar.';
                return $array; 
            }

        $user->save();
        
        $array['success'] = 'Seu perfil foi editado com sucesso.';

        return $array;
    }

    public function editUserAvatar(Request $request){
        $array = ['error' => ''];
        
        if($request->hasFile('file')){
            
            $pathName = md5(time().rand(0,1000)).'.jpg';
            move_uploaded_file($_FILES['file']['tmp_name'], 'media/'.$pathName);

            $user = User::find($request->id);
                $user->avatar = url('/media').'/'.$pathName;
            $user->save();

        }

        return $array;
    }

}
