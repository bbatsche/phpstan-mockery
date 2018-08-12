<?php declare(strict_types = 1);

namespace PHPStan\Mockery\Type;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;

class AllowsDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{

	public function getClass(): string
	{
		return \Mockery\MockInterface::class;
	}

	public function isMethodSupported(MethodReflection $methodReflection): bool
	{
		return $methodReflection->getName() === 'allows';
	}

	public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
	{
		$calledOnType = $scope->getType($methodCall->var);
		$defaultType = ParametersAcceptorSelector::selectSingle($methodReflection->getVariants())->getReturnType();
		if (!$calledOnType instanceof IntersectionType || count($calledOnType->getTypes()) !== 2) {
			return $defaultType;
		}
		$mockedType = $calledOnType->getTypes()[1];
		if (!$mockedType instanceof TypeWithClassName) {
			return $defaultType;
		}

		return TypeCombinator::intersect(
			new AllowsObjectType($mockedType->getClassName()),
			new ObjectType(Allows::class)
		);
	}

}
